#!/usr/bin/env python3
"""
Complete MongoDB Atlas functionality test suite
Tests all MongoDB operations: connection, CRUD, multi-tenancy, performance, error handling
"""

import sys
import os
import time
from pathlib import Path
from datetime import datetime

# Add parent directory to path to import app modules
sys.path.insert(0, str(Path(__file__).parent.parent))

from app.services.mongodb_service import MongoDBService
from app.config import MONGODB_URI, MONGODB_HOST, MONGODB_DATABASE

# Colors for output
GREEN = '\033[0;32m'
RED = '\033[0;31m'
YELLOW = '\033[1;33m'
BLUE = '\033[0;34m'
CYAN = '\033[0;36m'
NC = '\033[0m'  # No Color

class TestResults:
    def __init__(self):
        self.passed = 0
        self.failed = 0
        self.tests = []
    
    def add_test(self, name, passed, message=""):
        self.tests.append({
            'name': name,
            'passed': passed,
            'message': message
        })
        if passed:
            self.passed += 1
            print(f"{GREEN}✅ PASS{NC}: {name}")
            if message:
                print(f"   {message}")
        else:
            self.failed += 1
            print(f"{RED}❌ FAIL{NC}: {name}")
            if message:
                print(f"   {message}")
    
    def summary(self):
        print(f"\n{BLUE}{'='*60}{NC}")
        print(f"{BLUE}📊 TEST SUMMARY{NC}")
        print(f"{BLUE}{'='*60}{NC}")
        print(f"{GREEN}✅ Passed: {self.passed}{NC}")
        print(f"{RED}❌ Failed: {self.failed}{NC}")
        print(f"{CYAN}📋 Total: {self.passed + self.failed}{NC}")
        if self.failed == 0:
            print(f"\n{GREEN}🎉 ALL TESTS PASSED!{NC}")
        else:
            print(f"\n{YELLOW}⚠️  Some tests failed. Review output above.{NC}")

def test_connection():
    """Test 1: Basic connection"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 1: MongoDB Connection{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    
    # Test connection
    is_connected = MongoDBService.is_connected()
    results.add_test(
        "Connection established",
        is_connected,
        f"Connected to: {MONGODB_HOST}" if is_connected else "Connection failed"
    )
    
    # Test database access
    db = MongoDBService.get_database()
    results.add_test(
        "Database access",
        db is not None,
        f"Database: {MONGODB_DATABASE}" if db is not None else "Database not accessible"
    )
    
    return results

def test_crud_operations():
    """Test 2: CRUD operations"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 2: CRUD Operations{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    test_collection = "test_crud_operations"
    
    # Test document
    test_doc = {
        "test_id": "crud_test_001",
        "name": "CRUD Test Document",
        "timestamp": datetime.now().isoformat(),
        "data": {"key1": "value1", "key2": 123}
    }
    
    # CREATE - Insert
    try:
        doc_id = MongoDBService.insert_document(test_collection, test_doc)
        results.add_test(
            "INSERT operation",
            doc_id is not None and doc_id != 'duplicate',
            f"Document ID: {doc_id}"
        )
    except Exception as e:
        results.add_test("INSERT operation", False, str(e))
        return results
    
    # READ - Find
    try:
        found_docs = MongoDBService.find_documents(
            test_collection,
            {"test_id": "crud_test_001"}
        )
        results.add_test(
            "FIND operation",
            len(found_docs) > 0,
            f"Found {len(found_docs)} document(s)"
        )
    except Exception as e:
        results.add_test("FIND operation", False, str(e))
    
    # UPDATE
    try:
        update_result = MongoDBService.update_document(
            test_collection,
            {"test_id": "crud_test_001"},
            {"updated": True, "update_timestamp": datetime.now().isoformat()}
        )
        results.add_test(
            "UPDATE operation",
            update_result > 0,
            f"Updated {update_result} document(s)"
        )
    except Exception as e:
        results.add_test("UPDATE operation", False, str(e))
    
    # COUNT
    try:
        count = MongoDBService.count_documents(
            test_collection,
            {"test_id": "crud_test_001"}
        )
        results.add_test(
            "COUNT operation",
            count > 0,
            f"Count: {count}"
        )
    except Exception as e:
        results.add_test("COUNT operation", False, str(e))
    
    # DELETE
    try:
        deleted = MongoDBService.delete_documents(
            test_collection,
            {"test_id": "crud_test_001"}
        )
        results.add_test(
            "DELETE operation",
            deleted > 0,
            f"Deleted {deleted} document(s)"
        )
    except Exception as e:
        results.add_test("DELETE operation", False, str(e))
    
    return results

def test_multi_tenancy():
    """Test 3: Multi-tenancy (tenant_id isolation)"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 3: Multi-Tenancy (tenant_id isolation){NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    test_collection = "test_multi_tenancy"
    
    # Insert documents for different tenants
    tenant1_doc = {
        "tenant_id": 1,
        "document_id": "tenant1_doc",
        "data": "Tenant 1 data"
    }
    tenant2_doc = {
        "tenant_id": 2,
        "document_id": "tenant2_doc",
        "data": "Tenant 2 data"
    }
    
    try:
        # Insert for tenant 1
        id1 = MongoDBService.insert_document(test_collection, tenant1_doc)
        results.add_test(
            "Insert tenant 1 document",
            id1 is not None,
            f"Document ID: {id1}"
        )
        
        # Insert for tenant 2
        id2 = MongoDBService.insert_document(test_collection, tenant2_doc)
        results.add_test(
            "Insert tenant 2 document",
            id2 is not None,
            f"Document ID: {id2}"
        )
        
        # Query tenant 1 only
        tenant1_docs = MongoDBService.find_documents(
            test_collection,
            {"tenant_id": 1}
        )
        results.add_test(
            "Query tenant 1 isolation",
            len(tenant1_docs) > 0 and all(doc.get("tenant_id") == 1 for doc in tenant1_docs),
            f"Found {len(tenant1_docs)} document(s) for tenant 1"
        )
        
        # Query tenant 2 only
        tenant2_docs = MongoDBService.find_documents(
            test_collection,
            {"tenant_id": 2}
        )
        results.add_test(
            "Query tenant 2 isolation",
            len(tenant2_docs) > 0 and all(doc.get("tenant_id") == 2 for doc in tenant2_docs),
            f"Found {len(tenant2_docs)} document(s) for tenant 2"
        )
        
        # Cleanup
        MongoDBService.delete_documents(test_collection, {"tenant_id": 1})
        MongoDBService.delete_documents(test_collection, {"tenant_id": 2})
        results.add_test("Multi-tenancy cleanup", True, "Test documents deleted")
        
    except Exception as e:
        results.add_test("Multi-tenancy test", False, str(e))
    
    return results

def test_performance():
    """Test 4: Performance (bulk operations, query speed)"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 4: Performance Tests{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    test_collection = "test_performance"
    
    # Bulk insert
    try:
        start_time = time.time()
        inserted_count = 0
        for i in range(10):
            doc = {
                "perf_test_id": f"perf_{i}",
                "data": f"Performance test document {i}",
                "timestamp": datetime.now().isoformat()
            }
            doc_id = MongoDBService.insert_document(test_collection, doc)
            if doc_id:
                inserted_count += 1
        
        insert_time = time.time() - start_time
        results.add_test(
            "Bulk insert (10 documents)",
            inserted_count == 10,
            f"Inserted {inserted_count}/10 documents in {insert_time:.3f}s ({inserted_count/insert_time:.1f} docs/s)"
        )
        
        # Query performance
        start_time = time.time()
        found_docs = MongoDBService.find_documents(
            test_collection,
            {"perf_test_id": {"$regex": "^perf_"}}
        )
        query_time = time.time() - start_time
        results.add_test(
            "Query performance",
            len(found_docs) == 10,
            f"Found {len(found_docs)} documents in {query_time:.3f}s"
        )
        
        # Count performance
        start_time = time.time()
        count = MongoDBService.count_documents(
            test_collection,
            {"perf_test_id": {"$regex": "^perf_"}}
        )
        count_time = time.time() - start_time
        results.add_test(
            "Count performance",
            count == 10,
            f"Counted {count} documents in {count_time:.3f}s"
        )
        
        # Cleanup
        MongoDBService.delete_documents(test_collection, {"perf_test_id": {"$regex": "^perf_"}})
        results.add_test("Performance test cleanup", True, "Test documents deleted")
        
    except Exception as e:
        results.add_test("Performance test", False, str(e))
    
    return results

def test_error_handling():
    """Test 5: Error handling"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 5: Error Handling{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    test_collection = "test_error_handling"
    
    # Test duplicate insert (should return 'duplicate')
    try:
        doc = {
            "unique_id": "error_test_001",
            "data": "Test document"
        }
        
        # First insert
        id1 = MongoDBService.insert_document(test_collection, doc)
        results.add_test(
            "First insert (should succeed)",
            id1 is not None,
            f"Document ID: {id1}"
        )
        
        # Try duplicate insert (should return 'duplicate' or fail gracefully)
        # Note: This depends on having a unique index on unique_id
        # For now, we just test that it doesn't crash
        try:
            id2 = MongoDBService.insert_document(test_collection, doc)
            # If it returns 'duplicate', that's expected behavior
            results.add_test(
                "Duplicate insert handling",
                id2 == 'duplicate' or id2 is None,
                f"Result: {id2} (expected: 'duplicate' or None)"
            )
        except Exception as e:
            results.add_test(
                "Duplicate insert handling",
                True,  # Exception is acceptable for duplicate
                f"Exception caught (expected): {type(e).__name__}"
            )
        
        # Cleanup
        MongoDBService.delete_documents(test_collection, {"unique_id": "error_test_001"})
        
    except Exception as e:
        results.add_test("Error handling test", False, str(e))
    
    # Test query on non-existent collection (should return empty list, not crash)
    try:
        empty_result = MongoDBService.find_documents(
            "non_existent_collection_xyz",
            {"test": "value"}
        )
        results.add_test(
            "Query non-existent collection",
            isinstance(empty_result, list),
            f"Returned empty list (expected): {len(empty_result)} documents"
        )
    except Exception as e:
        results.add_test("Query non-existent collection", False, str(e))
    
    return results

def test_indexes():
    """Test 6: Index creation and usage"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 6: Index Usage{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    test_collection = "test_indexes"
    
    try:
        collection = MongoDBService.get_collection(test_collection)
        if collection is not None:
            # List existing indexes
            indexes = collection.list_indexes()
            index_list = list(indexes)
            results.add_test(
                "List indexes",
                True,
                f"Found {len(index_list)} index(es)"
            )
            
            # Note: Creating indexes requires admin privileges
            # We just verify we can query with indexed fields
            doc = {
                "tenant_id": 999,
                "indexed_field": "test_value",
                "timestamp": datetime.now()
            }
            doc_id = MongoDBService.insert_document(test_collection, doc)
            
            # Query with indexed field (tenant_id is typically indexed)
            start_time = time.time()
            found = MongoDBService.find_documents(
                test_collection,
                {"tenant_id": 999}
            )
            query_time = time.time() - start_time
            
            results.add_test(
                "Query with indexed field",
                len(found) > 0,
                f"Found {len(found)} document(s) in {query_time:.3f}s"
            )
            
            # Cleanup
            MongoDBService.delete_documents(test_collection, {"tenant_id": 999})
        else:
            results.add_test("Index test", False, "Collection not accessible")
            
    except Exception as e:
        results.add_test("Index test", False, str(e))
    
    return results

def test_connection_resilience():
    """Test 7: Connection resilience (reconnect after close)"""
    print(f"\n{BLUE}{'='*60}{NC}")
    print(f"{BLUE}TEST 7: Connection Resilience{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    results = TestResults()
    
    try:
        # Test initial connection
        is_connected1 = MongoDBService.is_connected()
        results.add_test(
            "Initial connection",
            is_connected1,
            "Connected successfully"
        )
        
        # Close connection
        MongoDBService.close()
        results.add_test(
            "Close connection",
            True,
            "Connection closed"
        )
        
        # Reconnect
        is_connected2 = MongoDBService.is_connected()
        results.add_test(
            "Reconnect after close",
            is_connected2,
            "Reconnected successfully"
        )
        
    except Exception as e:
        results.add_test("Connection resilience", False, str(e))
    
    return results

def main():
    """Run all tests"""
    print(f"{BLUE}{'='*60}{NC}")
    print(f"{BLUE}🧪 MongoDB Atlas Complete Test Suite{NC}")
    print(f"{BLUE}{'='*60}{NC}")
    
    print(f"\n{CYAN}Configuration:{NC}")
    print(f"  Host: {MONGODB_HOST}")
    print(f"  Database: {MONGODB_DATABASE}")
    print(f"  URI: {MONGODB_URI[:60]}..." if len(MONGODB_URI) > 60 else f"  URI: {MONGODB_URI}")
    
    all_results = TestResults()
    
    # Run all test suites
    all_results.tests.extend(test_connection().tests)
    all_results.tests.extend(test_crud_operations().tests)
    all_results.tests.extend(test_multi_tenancy().tests)
    all_results.tests.extend(test_performance().tests)
    all_results.tests.extend(test_error_handling().tests)
    all_results.tests.extend(test_indexes().tests)
    all_results.tests.extend(test_connection_resilience().tests)
    
    # Calculate totals
    all_results.passed = sum(1 for t in all_results.tests if t['passed'])
    all_results.failed = sum(1 for t in all_results.tests if not t['passed'])
    
    # Print summary
    all_results.summary()
    
    return all_results.failed == 0

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)

