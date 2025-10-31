"""
System router - Health checks and service management
"""

import subprocess
import sys
from pathlib import Path
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from typing import Dict, Any

router = APIRouter()


class ServiceStatus(BaseModel):
    service: str
    status: str
    port: int
    url: str


class SystemStatusResponse(BaseModel):
    status: str
    services: list[ServiceStatus]
    message: str


def _check_port(host: str, port: int, timeout: float = 2.0) -> bool:
    """Check if a port is open and accepting connections"""
    import socket
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        result = sock.connect_ex((host, port))
        sock.close()
        return result == 0
    except Exception:
        return False


@router.get("/system/status", response_model=SystemStatusResponse)
async def get_system_status():
    """
    Get status of all required services
    """
    services = []
    all_running = True
    
    # Check Python FastAPI (this service - if we're here, it's running)
    # Also verify port is accessible
    python_port = 9000  # Default, can be from env
    if _check_port('127.0.0.1', python_port):
        services.append(ServiceStatus(
            service="python_fastapi",
            status="running",
            port=python_port,
            url=f"http://localhost:{python_port}"
        ))
    else:
        all_running = False
        services.append(ServiceStatus(
            service="python_fastapi",
            status="error",
            port=python_port,
            url=f"http://localhost:{python_port}"
        ))
    
    # Check Frontend (optional, harder to check from backend)
    # We'll check if port 5173 is listening
    frontend_port = 5173
    if _check_port('127.0.0.1', frontend_port):
        services.append(ServiceStatus(
            service="frontend_vite",
            status="running",
            port=frontend_port,
            url=f"http://localhost:{frontend_port}"
        ))
    else:
        services.append(ServiceStatus(
            service="frontend_vite",
            status="stopped",
            port=frontend_port,
            url=f"http://localhost:{frontend_port}"
        ))
        # Frontend is optional, so we don't mark all_running = False
    
    return SystemStatusResponse(
        status="ok" if all_running else "degraded",
        services=services,
        message="All services running" if all_running else "Some services are not running"
    )


@router.post("/system/ensure-services")
async def ensure_services():
    """
    Ensure all required services are running
    Executes ensure_services.sh script to check and start services
    """
    try:
        # Get project root (3 levels up from this file)
        project_root = Path(__file__).parent.parent.parent.parent
        ensure_script = project_root / "ensure_services.sh"
        
        if not ensure_script.exists():
            raise HTTPException(
                status_code=500,
                detail=f"ensure_services.sh not found at {ensure_script}"
            )
        
        # Execute script
        result = subprocess.run(
            ["bash", str(ensure_script)],
            capture_output=True,
            text=True,
            cwd=str(project_root),
            timeout=30
        )
        
        if result.returncode == 0:
            return {
                "status": "success",
                "message": "Services ensured",
                "output": result.stdout
            }
        else:
            raise HTTPException(
                status_code=500,
                detail=f"Failed to ensure services: {result.stderr}"
            )
    
    except subprocess.TimeoutExpired:
        raise HTTPException(
            status_code=500,
            detail="Service startup timeout"
        )
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error ensuring services: {str(e)}"
        )

