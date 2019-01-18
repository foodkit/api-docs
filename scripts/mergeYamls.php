<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 16 Jan 2019
 */
declare(strict_types=1);

require_once "../vendor/autoload.php";

/**
 * This scripts accepts a number of paths to yaml files.
 * It joins all paths from these files into a single huge yaml file.
 */

$files = [
    __DIR__ . "/../src/admin/loyalty/vip.yaml",
    __DIR__ . "/../src/admin/brands/alerts.yaml",
];
$tmpFolder = __DIR__ . "/../tmp";
$tmpFilesBuffer = [];
$mainFileTemplate = __DIR__ . "/../src/admin/all.yaml";
$mergedFile = "$tmpFolder/admin.merged.yaml";

$swagger_cli = __DIR__ . "/../node_modules/.bin/swagger-cli";
$speccy = __DIR__ . "/../node_modules/.bin/speccy";
$yaml2json = __DIR__ . "/../node_modules/.bin/yaml2json";

# 0. Validate input files
echo "Validate YAML Files\n";
foreach ($files as $file) {
    echo realpath($file);
    exec("$swagger_cli validate $file", $output, $retCode);
    if ($retCode) {
        return 1;
    } else {
        echo ": VALID\n";
    }
}
echo "\n\n";

# 1. Pull in any existing references
echo "Resolve References\n";
foreach ($files as $file) {
    echo realpath($file);
    $tmpFile = "$tmpFolder/" . md5(random_bytes(32)) . ".yaml";
    exec("$speccy resolve $file -o $tmpFile", $output, $retCode);
    if ($retCode) {
        return 1;
    } else {
        echo ": RESOLVED\n";
        $tmpFilesBuffer[] = $tmpFile;
    }
}
echo "\n\n";

# 2. Merge all together
# 2.1 Convert Yaml tmp files to Json files
foreach ($tmpFilesBuffer as $i => $tmpFile) {
    exec("$yaml2json $tmpFile > $tmpFile.json");
    unlink($tmpFile);
    $tmpFilesBuffer[$i] = "$tmpFile.json";
}

# 2.2 Parse and merge
$mergedPaths = [];
foreach ($tmpFilesBuffer as $tmpFile) {
    $openapi = new \HKarlstrom\OpenApiReader\OpenApiReader($tmpFile);
    $mergedPaths += $openapi->json->get('paths');
}

# 3. Output the final file
$mainFileJSON = json_decode(`$yaml2json $mainFileTemplate`, true);
$mainFileJSON['paths'] = $mergedPaths;
file_put_contents($mergedFile, json_encode($mainFileJSON, JSON_PRETTY_PRINT));
echo "Merged to: " . realpath($mergedFile) . "\n";

# 4. Clean up
foreach ($tmpFilesBuffer as $tmpFile) {
    unlink($tmpFile);
}

echo "Done.";