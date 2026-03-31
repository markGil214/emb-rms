<?php
// CLI Test for ShelfMapApiController
require 'vendor/autoload.php';

$pathsConfig = require 'app/Config/Paths.php';
$paths = new Config\Paths();

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOTPATH', $paths->rootPath);
define('SYSTEMPATH', $paths->systemPath);
define('APPPATH', $paths->appPath);

$_SERVER['CI_ENVIRONMENT'] = getenv('CI_ENVIRONMENT') ?? 'development';

// Create CodeIgniter instance
$app = require rtrim(SYSTEMPATH, '/ ') . '/framework.php';

try {
    // Initialize database
    $db = \Config\Database::connect();
    echo "✓ Database connected\n";

    // Check tables
    $tables = $db->listTables();
    echo "✓ Found " . count($tables) . " tables\n";

    // Check for locations table
    if (in_array('locations', $tables)) {
        $locCount = $db->table('locations')->countAllResults();
        echo "✓ Locations table has " . $locCount . " records\n";
    } else {
        echo "✗ Locations table not found\n";
        exit(1);
    }

    // Check for folders table
    if (in_array('folders', $tables)) {
        $folderCount = $db->table('folders')->countAllResults();
        echo "✓ Folders table has " . $folderCount . " records\n";
    } else {
        echo "✗ Folders table not found\n";
        exit(1);
    }

    // Test the controller
    echo "\n=== Testing ShelfMapApiController ===\n";
    $controller = new \App\Controllers\Api\ShelfMapApiController();
    
    // Create a mock response object
    $response = service('response');
    
    // Set response on controller
    $controller->response = $response;

    // Call the method
    $result = $controller->getShelfData();
    
    // Get the response body
    $body = $result->getBody();
    $data = json_decode($body, true);

    if ($data['status'] === 'success') {
        echo "✓ API returned success\n";
        echo "  - Areas: " . count($data['data']['areas']) . "\n";
        echo "  - Shelves: " . count($data['data']['shelves']) . "\n";
        echo "  - Locations: " . count($data['data']['locations']) . "\n";
        echo "  - Total Folders: " . ($data['data']['totalFolders'] ?? 0) . "\n";
        
        if (!empty($data['data']['areas'])) {
            echo "\n✓ Sample Area Data:\n";
            $sample = $data['data']['areas'][0];
            echo "  - ID: " . $sample['id'] . "\n";
            echo "  - Name: " . $sample['name'] . "\n";
            echo "  - Occupied: " . $sample['data']['occupied'] . " / " . $sample['data']['capacity'] . "\n";
            echo "  - Status: " . $sample['data']['status'] . "\n";
        }
    } else {
        echo "✗ API returned error: " . $data['message'] . "\n";
        exit(1);
    }

    echo "\n✓ All tests passed!\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    exit(1);
}
