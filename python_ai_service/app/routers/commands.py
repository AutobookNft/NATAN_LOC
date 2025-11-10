"""
Commands Router - endpoints per comandi chat diretti su MongoDB
"""

from __future__ import annotations

import copy
import json
import time
from collections import deque
import os
from datetime import datetime, time as dt_time, timezone
from threading import Lock
from typing import Any, Dict, List, Optional, Tuple

import logging

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field, validator

from app.config.natural_query import (
    BLACKLIST as NATURAL_QUERY_BLACKLIST,
    DECAY_MINUTES as NATURAL_QUERY_DECAY_MINUTES,
    MAX_ATTEMPTS as NATURAL_QUERY_MAX_ATTEMPTS,
)
from app.services.ai_router import AIRouter
from app.services.mongodb_service import MongoDBService
from zoneinfo import ZoneInfo

LOCAL_TIMEZONE = ZoneInfo(os.getenv("NATURAL_QUERY_TIMEZONE", "Europe/Rome"))
from pymongo import ASCENDING, DESCENDING

router = APIRouter(prefix="/commands", tags=["commands"])
ai_router = AIRouter()
logger = logging.getLogger(__name__)

_rate_limit_window_seconds = max(NATURAL_QUERY_DECAY_MINUTES * 60, 60)
_rate_limit_buckets: Dict[str, deque] = {}
_rate_limit_lock = Lock()
_ALLOWED_OPERATORS = {
    "$and",
    "$or",
    "$in",
    "$nin",
    "$gte",
    "$lte",
    "$gt",
    "$lt",
    "$regex",
    "$eq",
    "$ne",
    "$exists",
    "$text",
    "$search",
    "$options",
    "$not",
}
_DAY_MS = 86_400_000


def _ensure_connection() -> None:
    if not MongoDBService.is_connected():
        raise HTTPException(
            status_code=503,
            detail="MongoDB connection is not available"
        )


def _protocol_date_to_iso(protocol_date: Any) -> Optional[str]:
    if protocol_date is None:
        return None
    try:
        # Mongo salva spesso epoch millisecondi (Int64)
        numeric_value = float(protocol_date)
        if numeric_value > 10_000_000_000:  # millisecondi
            numeric_value = numeric_value / 1000.0
        dt = datetime.utcfromtimestamp(numeric_value)
        return dt.isoformat()
    except Exception:
        return None


def _build_protocol_match(protocol_numbers: List[str]) -> Dict[str, Any]:
    cleaned = [numero for numero in protocol_numbers if numero]
    if not cleaned:
        return {}
    return {
        "$or": [
            {"protocol_number": {"$in": cleaned}},
            {"metadata.numero_atto": {"$in": cleaned}},
        ]
    }


def _document_to_payload(document: Dict[str, Any]) -> Dict[str, Any]:
    metadata = document.get("metadata", {}) or {}
    protocol_number = (
        document.get("protocol_number")
        or metadata.get("numero_atto")
        or metadata.get("protocollo")
    )
    protocol_date = document.get("protocol_date") or metadata.get("data_atto")

    department = (
        document.get("department")
        or metadata.get("dipartimento")
        or metadata.get("department")
        or metadata.get("direzione")
    )

    document_type = (
        document.get("document_type")
        or metadata.get("tipo_atto")
        or metadata.get("categoria")
    )

    return {
        "document_id": document.get("document_id"),
        "title": document.get("title") or metadata.get("title") or "",
        "description": document.get("description"),
        "protocol_number": protocol_number,
        "protocol_date_iso": _protocol_date_to_iso(protocol_date),
        "document_type": document_type or "",
        "department": department or "",
        "raw": {
            "metadata": metadata,
        },
    }


def _shorten(text: Optional[str], limit: int = 320) -> Optional[str]:
    if not text:
        return None
    text = text.strip()
    if len(text) <= limit:
        return text
    return f"{text[:limit - 3].rstrip()}..."


def _check_blacklist(text: str) -> Optional[str]:
    lowered = text.lower()
    for term in NATURAL_QUERY_BLACKLIST:
        term = term.strip().lower()
        if term and term in lowered:
            return term
    return None


def _check_rate_limit(bucket_key: str) -> Tuple[bool, float]:
    now = time.time()
    with _rate_limit_lock:
        bucket = _rate_limit_buckets.setdefault(bucket_key, deque())
        while bucket and now - bucket[0] > _rate_limit_window_seconds:
            bucket.popleft()

        if len(bucket) >= NATURAL_QUERY_MAX_ATTEMPTS:
            retry_after = _rate_limit_window_seconds - (now - bucket[0])
            return False, max(retry_after, 0.0)

        bucket.append(now)
    return True, 0.0


def _validate_filter(value: Any) -> None:
    if isinstance(value, dict):
        for key, nested in value.items():
            if isinstance(key, str) and key.startswith("$"):
                if key not in _ALLOWED_OPERATORS:
                    raise ValueError(f"Operator {key} is not allowed")
            _validate_filter(nested)
    elif isinstance(value, list):
        for item in value:
            _validate_filter(item)


def _prepare_sort(sort_value: Any) -> List[Tuple[str, int]]:
    if not sort_value:
        return []

    result: List[Tuple[str, int]] = []

    if isinstance(sort_value, list):
        for entry in sort_value:
            field = None
            direction = "desc"

            if isinstance(entry, (list, tuple)) and len(entry) == 2:
                field = str(entry[0]).strip()
                direction = str(entry[1]).strip().lower()
            elif isinstance(entry, dict) and entry:
                field, direction = next(iter(entry.items()))
                field = str(field).strip()
                direction = str(direction).strip().lower()

            if not field:
                continue

            pymongo_direction = DESCENDING
            if direction in {"asc", "1", "ascending"}:
                pymongo_direction = ASCENDING

            result.append((field, pymongo_direction))

    return result


def _normalize_filter(filter_value: Optional[Dict[str, Any]], tenant_id: int) -> Dict[str, Any]:
    filter_value = filter_value or {}
    if not isinstance(filter_value, dict):
        raise ValueError("Filter must be a JSON object")

    if not filter_value:
        return {"tenant_id": tenant_id}

    # Force tenant filter preserving existing conditions
    if "tenant_id" in filter_value:
        filter_value["tenant_id"] = tenant_id
        result = filter_value
    else:
        result = {
        "$and": [
            filter_value,
            {"tenant_id": tenant_id},
        ],
    }

    _convert_protocol_dates(result)
    _extend_date_targets(result)
    return result


def _convert_protocol_dates(value: Any) -> None:
    if isinstance(value, dict):
        for key, nested in list(value.items()):
            if key == "protocol_date":
                if isinstance(nested, dict):
                    for op_key, op_val in list(nested.items()):
                        nested[op_key] = _parse_date_value(op_val, implicit_range=False)
                else:
                    parsed = _parse_date_value(nested, implicit_range=True)
                    if isinstance(parsed, dict) and any(k in parsed for k in ("$gte", "$lte", "$gt", "$lt")):
                        value[key] = parsed
                    else:
                        value[key] = parsed
            elif key == "document_type":
                value[key] = _map_document_type(nested)
            else:
                _convert_protocol_dates(nested)
    elif isinstance(value, list):
        for item in value:
            _convert_protocol_dates(item)


def _extend_date_targets(value: Any) -> None:
    if isinstance(value, dict):
        for key, nested in list(value.items()):
            if key == "protocol_date":
                condition = value.pop("protocol_date")
                or_condition = {
                    "$or": [
                        {"protocol_date": condition},
                        {"metadata.data_atto": copy.deepcopy(condition)},
                    ]
                }

                if not value:
                    value.update(or_condition)
                else:
                    value.setdefault("$and", []).append(or_condition)
            else:
                _extend_date_targets(nested)
    elif isinstance(value, list):
        for index, item in enumerate(list(value)):
            if isinstance(item, dict) and "protocol_date" in item and len(item) == 1:
                condition = item["protocol_date"]
                value[index] = {
                    "$or": [
                        {"protocol_date": condition},
                        {"metadata.data_atto": copy.deepcopy(condition)},
                    ]
                }
            else:
                _extend_date_targets(item)


def _parse_date_value(raw: Any, *, implicit_range: bool) -> Any:
    if isinstance(raw, (int, float)):
        return raw
    if isinstance(raw, str):
        raw = raw.strip()
        if not raw:
            return raw
        try:
            dt = datetime.fromisoformat(raw)
            if dt.tzinfo is None:
                dt = dt.replace(tzinfo=LOCAL_TIMEZONE).astimezone(timezone.utc)
            if implicit_range:
                start_local = datetime.combine(dt.astimezone(LOCAL_TIMEZONE).date(), dt_time.min, tzinfo=LOCAL_TIMEZONE)
                end_local = datetime.combine(dt.astimezone(LOCAL_TIMEZONE).date(), dt_time.max, tzinfo=LOCAL_TIMEZONE)
                start = start_local.astimezone(timezone.utc)
                end = end_local.astimezone(timezone.utc)
                return {
                    "$gte": int(start.timestamp() * 1000),
                    "$lte": int(end.timestamp() * 1000),
                }
            return int(dt.timestamp() * 1000)
        except ValueError:
            # Try only date part
            try:
                dt = datetime.strptime(raw, "%Y-%m-%d")
                if implicit_range:
                    start_local = datetime.combine(dt.date(), dt_time.min, tzinfo=LOCAL_TIMEZONE)
                    end_local = datetime.combine(dt.date(), dt_time.max, tzinfo=LOCAL_TIMEZONE)
                    start = start_local.astimezone(timezone.utc)
                    end = end_local.astimezone(timezone.utc)
                    return {
                        "$gte": int(start.timestamp() * 1000),
                        "$lte": int(end.timestamp() * 1000),
                    }
                dt = datetime.combine(dt.date(), dt_time.min, tzinfo=LOCAL_TIMEZONE).astimezone(timezone.utc)
                return int(dt.timestamp() * 1000)
            except ValueError:
                return raw
    return raw


def _map_document_type(raw: Any) -> Any:
    if isinstance(raw, dict) and "$regex" in raw:
        pattern = str(raw["$regex"]).lower()
        if "delib" in pattern or "deliber" in pattern:
            return "pa_act"
        if "determ" in pattern or "determin" in pattern:
            return "pa_act"
    if isinstance(raw, str):
        lowered = raw.lower()
        if lowered in {"delibera", "deliberazione", "deliberazioni"}:
            return "pa_act"
    return raw


def _extract_json(content: str) -> str:
    content = content.strip()
    if content.startswith("```"):
        segments = content.split("```")
        if len(segments) >= 3:
            return segments[1].split("\n", 1)[-1] if segments[1].startswith("json") else segments[1]
    return content


def _build_rag_hint(text: str, count: int) -> Dict[str, Any]:
    lowered = text.lower()
    analysis_keywords = (
        "perché",
        "perche",
        "spiega",
        "analizza",
        "riassu",
        "commenta",
        "confronta",
        "come ",
        "come?",
        "come posso",
        "come puoi",
        "come può",
        "in che modo",
        "quali sono",
        "qual è",
        "qual e",
        "descrivi",
        "illustra",
        "suggerisci",
        "motiva",
        "per quale motivo",
        "cosa puoi",
        "cosa possiamo",
        "cosa si può",
        "cosa si puo",
        "come si può",
        "come si puo",
        "strategia",
        "strategie",
        "migliorare",
        "migliora",
        "miglioramento",
        "ottimizzare",
        "ottimizza",
        "ottimizzazione",
        "efficientare",
        "efficienti",
        "supportare",
        "supporto",
        "aiutare",
        "consiglia",
        "proponi",
        "indica",
    )

    if any(keyword in lowered for keyword in analysis_keywords):
        return {"should_run": True, "reason": "analysis_intent"}

    if count == 0:
        return {"should_run": False, "reason": "no_results"}

    return {"should_run": False}


def _apply_date_range_fallback(
    filter_query: Dict[str, Any],
    *,
    days_before: int = 5,
    days_after: int = 5,
) -> Tuple[Dict[str, Any], Optional[Dict[str, Any]]]:
    """
    Extend date ranges when no results are found to capture nearby acts.
    """
    if not filter_query:
        return filter_query, None

    expanded_filter = copy.deepcopy(filter_query)
    ranges: List[Dict[str, Any]] = []

    def _walk(node: Any) -> None:
        if isinstance(node, dict):
            for key, value in node.items():
                if key in {"protocol_date", "metadata.data_atto"} and isinstance(value, dict):
                    original_start = value.get("$gte")
                    if original_start is None:
                        original_start = value.get("$gt")
                    original_end = value.get("$lte")
                    if original_end is None:
                        original_end = value.get("$lt")

                    if original_start is None and original_end is None:
                        continue

                    # Ensure numeric values
                    if original_start is not None:
                        try:
                            original_start = int(original_start)
                        except (TypeError, ValueError):
                            original_start = None
                    if original_end is not None:
                        try:
                            original_end = int(original_end)
                        except (TypeError, ValueError):
                            original_end = None

                    if original_start is None and original_end is None:
                        continue

                    if (
                        original_start is not None
                        and original_end is not None
                        and original_end >= original_start
                        and (original_end - original_start) > 31 * _DAY_MS
                    ):
                        continue

                    mutated = False
                    new_start = original_start
                    new_end = original_end

                    if original_start is not None:
                        new_start = max(0, original_start - days_before * _DAY_MS)
                        if new_start != original_start:
                            value["$gte"] = new_start
                            value.pop("$gt", None)
                            mutated = True

                    if original_end is not None:
                        new_end = original_end + days_after * _DAY_MS
                        if new_end != original_end:
                            value["$lte"] = new_end
                            value.pop("$lt", None)
                            mutated = True

                    if mutated:
                        ranges.append(
                            {
                                "field": key,
                                "original": {"start": original_start, "end": original_end},
                                "expanded": {"start": new_start, "end": new_end},
                            }
                        )

                if isinstance(value, (dict, list)):
                    _walk(value)
        elif isinstance(node, list):
            for item in node:
                _walk(item)

    _walk(expanded_filter)

    if not ranges:
        return filter_query, None

    return expanded_filter, {
        "type": "expanded_date_range",
        "days_before": days_before,
        "days_after": days_after,
        "ranges": ranges,
    }


class CommandBaseRequest(BaseModel):
    tenant_id: int = Field(..., ge=1)


class AttoCommandRequest(CommandBaseRequest):
    document_id: Optional[str] = None
    protocol_number: Optional[str] = None

    @validator("document_id", "protocol_number", pre=True, always=True)
    def strip_values(cls, value: Optional[str]) -> Optional[str]:
        if isinstance(value, str):
            value = value.strip()
            return value or None
        return value

    def filters(self) -> Dict[str, Any]:
        base_filter: Dict[str, Any] = {"tenant_id": self.tenant_id}

        if self.document_id:
            base_filter["document_id"] = self.document_id

        if self.protocol_number:
            base_filter["$or"] = [
                {"protocol_number": self.protocol_number},
                {"metadata.numero_atto": self.protocol_number},
            ]

        return base_filter


class AttiCommandRequest(CommandBaseRequest):
    document_ids: Optional[List[str]] = None
    protocol_numbers: Optional[List[str]] = None
    types: Optional[List[str]] = None
    departments: Optional[List[str]] = None
    responsibles: Optional[List[str]] = None
    text: Optional[str] = None
    date_from: Optional[str] = None
    date_to: Optional[str] = None
    limit: Optional[int] = Field(default=10, ge=1, le=100)

    @validator(
        "document_ids",
        "protocol_numbers",
        "types",
        "departments",
        "responsibles",
        pre=True,
    )
    def ensure_list(cls, value):
        if value is None:
            return None
        if isinstance(value, str):
            return [value]
        return [item for item in value if item]

    @validator("text", "date_from", "date_to", pre=True)
    def strip_optional(cls, value):
        if isinstance(value, str):
            value = value.strip()
            return value or None
        return value


class StatsCommandRequest(CommandBaseRequest):
    date_from: Optional[str] = None
    date_to: Optional[str] = None
    limit: Optional[int] = Field(default=10, ge=1, le=100)
    scraper_type: Optional[str] = None

    @validator("date_from", "date_to", pre=True)
    def strip_optional(cls, value):
        if isinstance(value, str):
            value = value.strip()
            return value or None
        return value

    @validator("scraper_type", pre=True)
    def normalize_scraper_type(cls, value):
        if isinstance(value, str):
            value = value.strip()
            return value or None
        return value


class NaturalQueryRequest(CommandBaseRequest):
    user_id: int = Field(..., ge=1)
    text: str = Field(..., min_length=3, max_length=2000)
    limit: Optional[int] = Field(default=None, ge=1, le=100)

    @validator("text", pre=True, always=True)
    def normalize_text(cls, value: str) -> str:
        value = value.strip()
        if not value:
            raise ValueError("Text cannot be empty")
        return value


class NaturalQueryModel(BaseModel):
    collection: str
    filter: Optional[Dict[str, Any]] = None
    limit: Optional[int] = None
    sort: Optional[Any] = None
    projection: Optional[Dict[str, Any]] = None

    @validator("collection")
    def ensure_collection(cls, value: str) -> str:
        if value != "documents":
            raise ValueError("Collection must be 'documents'")
        return value


@router.post("/natural-query")
async def command_natural_query(request: NaturalQueryRequest):
    _ensure_connection()
    logger.info(
        "[commands][natural-query] received request tenant=%s user=%s limit=%s text=%s"
        % (request.tenant_id, request.user_id, request.limit, request.text)
    )

    blocked_term = _check_blacklist(request.text)
    if blocked_term:
        return {
            "success": False,
            "code": "blacklisted",
            "message": "Request contains forbidden term.",
            "term": blocked_term,
        }

    allowed, retry_after = _check_rate_limit(str(request.user_id))
    if not allowed:
        retry_minutes = max(int(retry_after // 60) + 1, 1)
        return {
            "success": False,
            "code": "throttled",
            "message": "Too many requests.",
            "retry_after_minutes": retry_minutes,
        }

    try:
        parsed_query = await _generate_structured_query(
            request.text,
            request.tenant_id,
            request.limit,
        )
    except ValueError as exc:
        return {
            "success": False,
            "code": "parse_failed",
            "message": "Unable to interpret request.",
            "reason": str(exc),
        }

    try:
        filter_query = _normalize_filter(parsed_query.filter, request.tenant_id)
        _validate_filter(filter_query)
    except ValueError as exc:
        return {
            "success": False,
            "code": "parse_failed",
            "message": "Filter not allowed.",
            "reason": str(exc),
        }

    limit = parsed_query.limit or 20
    limit = max(1, min(limit, 100))
    sort_spec = _prepare_sort(parsed_query.sort)

    fallback_details: Optional[Dict[str, Any]] = None

    documents = MongoDBService.find_documents(
        parsed_query.collection,
        filter_query,
        limit=limit,
        sort=sort_spec if sort_spec else None,
    )
    logger.info(
        "[commands][natural-query] primary query executed limit=%s count=%s filter=%s"
        % (limit, len(documents), filter_query)
    )

    if not documents:
        fallback_filter, fallback_details = _apply_date_range_fallback(filter_query)
        if fallback_details is not None:
            documents = MongoDBService.find_documents(
                parsed_query.collection,
                fallback_filter,
                limit=limit,
                sort=sort_spec if sort_spec else None,
            )
            if documents:
                filter_query = fallback_filter
                logger.info(
                    "[commands][natural-query] fallback successful count=%s details=%s"
                    % (len(documents), fallback_details)
                )
            else:
                fallback_details = None
                logger.info(
                    "[commands][natural-query] fallback yielded no documents details=%s"
                    % fallback_details
                )
        else:
            logger.info(
                "[commands][natural-query] no fallback applied filter=%s" % filter_query
            )

    payload_documents: List[Dict[str, Any]] = []
    for document in documents:
        payload = _document_to_payload(document)
        description = payload.get("description") or (
            (document.get("content") or {}).get("raw_text") if isinstance(document.get("content"), dict) else ""
        )

        payload_documents.append(
            {
                "document_id": payload.get("document_id"),
                "title": payload.get("title"),
                "description": _shorten(description),
                "protocol_number": payload.get("protocol_number"),
                "protocol_date": payload.get("protocol_date_iso"),
                "document_type": payload.get("document_type"),
                "department": payload.get("department"),
                "blockchain_anchored": bool(document.get("blockchain_anchored", False)),
            }
        )

    rag_hint = _build_rag_hint(request.text, len(payload_documents))

    if not payload_documents:
        extra_data = {
            "tenant_id": request.tenant_id,
            "user_id": request.user_id,
            "original_text": request.text,
            "filter": filter_query,
            "fallback": fallback_details,
        }
        logger.info("[commands][natural-query] no results details=%s", extra_data)
        return {
            "success": False,
            "code": "no_results",
            "message": "Nessun documento trovato",
            "documents": [],
            "limit": limit,
            "total": 0,
            "original_text": request.text,
            "query": filter_query,
            "normalized_query": request.text,
            "rag_hint": rag_hint,
            "fallback": fallback_details,
        }

    success_payload = {
        "success": True,
        "documents": payload_documents,
        "limit": limit,
        "total": len(payload_documents),
        "original_text": request.text,
        "query": filter_query,
        "normalized_query": request.text,
        "rag_hint": rag_hint,
        "fallback": fallback_details,
    }
    logger.info(
        "[commands][natural-query] success original_text=%s count=%s query=%s"
        % (request.text, len(payload_documents), filter_query)
    )
    return success_payload


async def _generate_structured_query(
    text: str,
    tenant_id: int,
    limit_override: Optional[int] = None,
) -> NaturalQueryModel:
    context = {
        "tenant_id": tenant_id,
        "task_class": "NATURAL_QUERY",
    }

    adapter = ai_router.get_chat_adapter(context)
    logger.info(
        "[commands][natural-query] generating structured query tenant=%s text=%s"
        % (tenant_id, text)
    )

    system_prompt = (
        "You translate Italian natural language requests into strict MongoDB JSON queries. "
        "Return ONLY compact JSON without explanations or markdown. "
        "Allowed keys: collection, filter, limit, sort. "
        "collection MUST be 'documents'. "
        "filter must be a JSON object using top-level fields like document_id, protocol_number, protocol_date, "
        "document_type, title, description, metadata.department, metadata.scraper_type. "
        "ONLY use operators: $and, $or, $in, $nin, $gte, $lte, $gt, $lt, $regex, $eq, $ne, $exists. "
        "Dates must be ISO strings (YYYY-MM-DD). "
        "limit must be between 1 and 100 (default 20). "
        "sort must be an array of [field, direction] pairs (direction asc/desc)."
    )

    user_prompt = (
        "Testo richiesta:\n"
        f"{text}\n\n"
        "Rispondi con JSON valido."
    )

    response = await adapter.generate(
        [
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": user_prompt},
        ],
        temperature=0.0,
        max_tokens=512,
    )
    logger.info("[commands][natural-query] model response raw=%s" % response)

    content = _extract_json(response.get("content", ""))

    try:
        data = json.loads(content)
    except json.JSONDecodeError as exc:
        raise ValueError(f"Invalid JSON from model: {exc}") from exc

    model = NaturalQueryModel(**data)
    logger.info("[commands][natural-query] structured query=%s" % model.model_dump())

    if limit_override is not None:
        model.limit = limit_override

    if model.limit is None:
        model.limit = 20

    return model


@router.post("/atto")
async def command_atto(request: AttoCommandRequest):
    _ensure_connection()

    filters = request.filters()
    documents = MongoDBService.find_documents(
        "documents",
        filters,
        limit=1,
    )

    if not documents:
        return {
            "success": False,
            "rows": [],
            "count": 0,
            "message": "Document not found",
        }

    payload = _document_to_payload(documents[0])

    return {
        "success": True,
        "rows": [payload],
        "count": 1,
    }


def _build_date_condition(date_from: Optional[str], date_to: Optional[str]) -> Dict[str, Any]:
    condition: Dict[str, Any] = {}

    def parse_date(value: str) -> Optional[datetime]:
        try:
            return datetime.fromisoformat(value)
        except Exception:
            return None

    if date_from:
        if epoch := parse_date(date_from):
            condition["$gte"] = epoch.timestamp() * 1000  # store in ms

    if date_to:
        if epoch := parse_date(date_to):
            condition["$lte"] = epoch.timestamp() * 1000

    return condition


@router.post("/atti")
async def command_atti(request: AttiCommandRequest):
    _ensure_connection()

    filters: List[Dict[str, Any]] = [{"tenant_id": request.tenant_id}]

    if request.document_ids:
        filters.append({"document_id": {"$in": request.document_ids}})

    if request.protocol_numbers:
        filters.append(_build_protocol_match(request.protocol_numbers))

    if request.types:
        filters.append(
            {
                "$or": [
                    {"document_type": {"$in": request.types}},
                    {"metadata.tipo_atto": {"$in": request.types}},
                ]
            }
        )

    if request.departments:
        filters.append(
            {
                "$or": [
                    {"department": {"$in": request.departments}},
                    {"metadata.dipartimento": {"$in": request.departments}},
                    {"metadata.department": {"$in": request.departments}},
                    {"metadata.direzione": {"$in": request.departments}},
                ]
            }
        )

    if request.responsibles:
        filters.append(
            {
                "$or": [
                    {"responsible": {"$in": request.responsibles}},
                    {"metadata.responsabile": {"$in": request.responsibles}},
                ]
            }
        )

    if request.text:
        filters.append(
            {
                "$or": [
                    {"title": {"$regex": request.text, "$options": "i"}},
                    {"description": {"$regex": request.text, "$options": "i"}},
                    {"metadata.title": {"$regex": request.text, "$options": "i"}},
                ]
            }
        )

    date_condition = _build_date_condition(request.date_from, request.date_to)
    if date_condition:
        filters.append(
            {
                "$or": [
                    {"protocol_date": date_condition},
                    {"metadata.data_atto": date_condition},
                ]
            }
        )

    query: Dict[str, Any]
    if len(filters) == 1:
        query = filters[0]
    else:
        query = {"$and": filters}

    documents = MongoDBService.find_documents(
        "documents",
        query,
        limit=request.limit,
        sort=[("protocol_date", -1), ("created_at", -1)],
    )

    rows = [_document_to_payload(doc) for doc in documents]

    return {
        "success": True,
        "rows": rows,
        "count": len(rows),
    }


@router.post("/stats")
async def command_stats(request: StatsCommandRequest):
    _ensure_connection()

    match_filters: List[Dict[str, Any]] = [{"tenant_id": request.tenant_id}]
    date_condition = _build_date_condition(request.date_from, request.date_to)

    if date_condition:
        match_filters.append(
            {
                "$or": [
                    {"protocol_date": date_condition},
                    {"metadata.data_atto": date_condition},
                ]
            }
        )

    if request.scraper_type:
        match_filters.append(
            {
                "$or": [
                    {"metadata.scraper_type": request.scraper_type},
                    {"scraper_type": request.scraper_type},
                ]
            }
        )

    if len(match_filters) == 1:
        match_stage: Dict[str, Any] = match_filters[0]
    else:
        match_stage = {"$and": match_filters}

    pipeline: List[Dict[str, Any]] = [
        {"$match": match_stage},
        {
            "$group": {
                "_id": "$document_type",
                "count": {"$sum": 1},
            }
        },
        {"$sort": {"count": -1}},
        {"$limit": request.limit or 10},
    ]

    collection = MongoDBService.get_collection("documents")
    if collection is None:
        raise HTTPException(status_code=500, detail="MongoDB collection not available")

    aggregated = list(collection.aggregate(pipeline))
    total_documents = collection.count_documents(match_stage)

    rows = [
        {
            "document_type": item.get("_id") or "",
            "count": int(item.get("count", 0)),
        }
        for item in aggregated
    ]

    return {
        "success": True,
        "rows": rows,
        "count": len(rows),
        "total_acts": int(total_documents),
    }


