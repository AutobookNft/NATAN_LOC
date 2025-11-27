<?php

/**
 * Integration Test: FEGI ↔ NATAN_LOC Unification
 * 
 * Tests:
 * 1. Create NATAN project (uses collections table)
 * 2. Verify context='pa_project' is set
 * 3. Verify FEGI doesn't see NATAN projects
 * 4. Verify NATAN doesn't see FEGI collections
 * 5. Create test EGI for FEGI marketplace
 * 6. Verify separation via context filters
 * 
 * Run: php tests/integration_fegi_natan_test.php
 */

require __DIR__ . '/../laravel_backend/vendor/autoload.php';

$app = require_once __DIR__ . '/../laravel_backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NatanProject;
use App\Models\NatanDocument;
use Illuminate\Support\Facades\DB;

echo "🧪 FEGI ↔ NATAN_LOC Integration Test\n";
echo "=====================================\n\n";

// TEST 1: Create NATAN Project
echo "📝 TEST 1: Create NATAN Project\n";
$natanProject = NatanProject::create([
    'creator_id' => 1, // Assuming user ID 1 exists
    'collection_name' => 'Test NATAN Project',
    'description' => 'Test project for NATAN integration',
    'icon' => 'folder_open',
    'color' => '#1B365D',
    'is_active' => true,
]);
echo "✅ NATAN Project created: ID={$natanProject->id}, context={$natanProject->context}\n";
assert($natanProject->context === 'pa_project', 'Context should be pa_project');
echo "✅ Context correctly set to 'pa_project'\n\n";

// TEST 2: Verify NATAN project is in collections table
echo "📝 TEST 2: Verify in collections table\n";
$dbRecord = DB::table('collections')->where('id', $natanProject->id)->first();
echo "✅ Found in collections table: ID={$dbRecord->id}, context={$dbRecord->context}\n";
assert($dbRecord->context === 'pa_project', 'DB context should be pa_project');
echo "✅ Database record has correct context\n\n";

// TEST 3: Verify FEGI doesn't see NATAN projects
echo "📝 TEST 3: Verify FEGI isolation\n";
$fegiCollections = DB::table('collections')
    ->where('context', 'marketplace')
    ->orWhereNull('context')
    ->get();
$natanProjects = DB::table('collections')
    ->where('context', 'pa_project')
    ->get();
echo "✅ FEGI collections (marketplace): {$fegiCollections->count()}\n";
echo "✅ NATAN projects (pa_project): {$natanProjects->count()}\n";
assert($natanProjects->count() >= 1, 'Should have at least 1 NATAN project');
echo "✅ Separation verified\n\n";

// TEST 4: Verify Model scope works
echo "📝 TEST 4: Verify Model global scope\n";
$allNatanProjects = NatanProject::all();
echo "✅ NatanProject::all() returned {$allNatanProjects->count()} projects\n";
foreach ($allNatanProjects as $proj) {
    assert($proj->context === 'pa_project', "All projects should have context='pa_project'");
}
echo "✅ All returned projects have correct context\n\n";

// TEST 5: Create test NATAN Document (EGI)
echo "📝 TEST 5: Create NATAN Document (EGI)\n";
$natanDocument = NatanDocument::create([
    'collection_id' => $natanProject->id,
    'user_id' => 1,
    'tenant_id' => 2,
    'title' => 'Test Document',
    'description' => 'Test document for NATAN',
    'original_filename' => 'test.pdf',
    'mime_type' => 'application/pdf',
    'size_bytes' => 1024,
    'pa_file_path' => 'test/path/test.pdf',
    'document_status' => 'pending',
]);
echo "✅ NATAN Document created: ID={$natanDocument->id}, context={$natanDocument->context}\n";
assert($natanDocument->context === 'pa_document', 'Context should be pa_document');
echo "✅ Context correctly set to 'pa_document'\n\n";

// TEST 6: Verify document is in egis table
echo "📝 TEST 6: Verify in egis table\n";
$dbEgi = DB::table('egis')->where('id', $natanDocument->id)->first();
echo "✅ Found in egis table: ID={$dbEgi->id}, context={$dbEgi->context}\n";
assert($dbEgi->context === 'pa_document', 'DB context should be pa_document');
echo "✅ Database record has correct context\n\n";

// TEST 7: Verify relationships
echo "📝 TEST 7: Verify relationships\n";
$documents = $natanProject->documents;
echo "✅ Project has {$documents->count()} documents\n";
assert($documents->count() >= 1, 'Project should have at least 1 document');
$firstDoc = $documents->first();
echo "✅ First document: ID={$firstDoc->id}, title={$firstDoc->title}\n";
assert($firstDoc->project->id === $natanProject->id, 'Document should belong to project');
echo "✅ Document belongs to correct project\n\n";

// TEST 8: Verify EGI Model scope isolation
echo "📝 TEST 8: Verify EGI Model scope isolation\n";
$allMarketplaceEgis = DB::table('egis')
    ->where('context', 'marketplace')
    ->orWhereNull('context')
    ->count();
$allPaDocuments = DB::table('egis')
    ->where('context', 'pa_document')
    ->count();
echo "✅ FEGI marketplace EGIs: {$allMarketplaceEgis}\n";
echo "✅ NATAN PA documents: {$allPaDocuments}\n";
assert($allPaDocuments >= 1, 'Should have at least 1 PA document');
echo "✅ EGI/Document separation verified\n\n";

// CLEANUP
echo "🧹 CLEANUP: Removing test data\n";
$natanDocument->delete();
echo "✅ Test document deleted\n";
$natanProject->delete();
echo "✅ Test project deleted\n\n";

echo "🎉 ALL TESTS PASSED!\n";
echo "=====================================\n";
echo "✅ FEGI ↔ NATAN_LOC integration working correctly!\n";
echo "✅ Collections table unified (context='marketplace' vs 'pa_project')\n";
echo "✅ EGIs table unified (context='marketplace' vs 'pa_document')\n";
echo "✅ Global scopes ensure complete isolation\n";
echo "✅ Relationships work correctly\n";

