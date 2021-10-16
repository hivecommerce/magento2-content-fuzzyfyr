#!/usr/bin/php
<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See https://github.com/magento/magento2/blob/develop/COPYING.txt for license details.
 */

/**
 * validate_m2_package.php - a script that checks a given M2 zip package to ensure
 * it is structured correctly and has all the required files.
 *
 *
 */



/**
 * @global array List of files to check for magento2-module packages.
 *
 */
$g_magentoModuleFiles = array(
    'etc/module.xml'
);
/**
 * @global array List of files to check for magento2-theme packages.
 *
 */
$g_themeFiles = array(
    'theme.xml'
);
/**
 * @global array List of files to check for magento2-theme packages.
 *
 */
$g_langFiles = array(
    'language.xml'
);
/**
 * @global array List of accepted package types to be validated in composer.json
 *               type field. The value here is not used.
 *
 */
$g_moduleTypes = array(
    'metapackage' => true,
    'magento2-module' => true,
    'magento2-theme' => true,
    'magento2-language' => true
);

/**
 * @global array List of Magento components or modules that an Extension developer must not depend on
 *               in the composer.json. The value here is not used.
 *
 */
$g_invalidDependencies = array(
    'magento/magento2-base' => true,
    'magento/magento2-ee-base' => true,
    'magento/product-community-edition' => true,
    'magento/product-enterprise-edition' => true
);

main($argc, $argv);

/**
 * Main entry point: process arguments and invokes validateM2Zip()
 *
 * It calls exit() with the following integer codes:
 *
 * 0   - Success; zip file was scanned and it passed all the checks.
 * 1   - No zip file name provided.
 * 2   - Some exception with stack trace.
 *
 * Other codes - @see validateM2Zip() below.
 *
 * @see usage()
 *
 * @see validateM2Zip()
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 *
 */
function main($argc, $argv)
{
    $opts = getopt('hd');

    if( isset($opts['h']) ) {
        usage();
        exit(0);
    }

    if( $argc < 2 ) {
        usage();
        exit(1);
    }

    $debug = isset($opts['d']);

    $zipFiles = getZipFiles($argv);

    if( count($zipFiles) == 0 ) {
        fwrite(STDERR, "ERROR: No zip files were detected. Please refer to the usage.\n");
        usage();
        exit(1);
    }

    // Exit code is non-zero if any of the supplied zip files
    // return a non-zero code.
    $rc = 0;

    foreach( $zipFiles as $zip ) {
        $rc2 = validateM2Zip($zip, $debug);

        if($rc2 != 0)
        {
            $rc = $rc2;
        }
    }

    exit($rc);
}

/**
 * Displays usage.
 *
 * @return void
 *
 */
function usage()
{
    echo <<<EOF
Usage: validate_m2_package [OPTIONS] <M2 zip file> [<M2 zip file> ...]

       -h  help
           Prints this usage.

       -d debug
           Optional - prints additional debug messages.

EOF;
}

/**
 * Parses the zip files given as arguments
 *
 * @param array $argv Command Line arguments
 *
 * @return array $zipFiles Names of the zip files.
 *
 */
function getZipFiles($argv)
{
    $zipFiles = [];
    // Getting rid of the script name
    array_shift($argv);

    foreach( $argv as $arg ) {
        if( $arg == '-d' ) {
            continue;
        }

        if( preg_match("/.*\.zip$/", $arg) ) {
            $zipFiles[] = $arg;
        }
        else {
            print "ERROR: \"$arg\" was skipped because it is not of the correct file format (.zip).\n";
        }
    }

    return $zipFiles;
}

/**
 * Validates the supplied M2 package zip file.
 *
 * The core logic starts here - the zip file name is opened and inspected for
 * for various checks as described below.
 *
 * The required files and module directory structure can be at the top-level,
 * or one level down from the top level directory. In addition, the required files
 * can be in an arbitrary folder (i.e. src/) given that they are mapped correctly in the
 * "composer.json", which should reside outside of this folder. If there is a top-level directory,
 * it is usually expected to  to be the same as the package name with the ".zip" extension.
 * So for example, for a package named MyExtension.zip, the required file "composer.json"
 * is expected at:
 *
 * unzip -l MyExtension.zip
 * .
 * etc/
 * composer.json
 * .
 * .
 *
 * OR
 *
 * unzip -l MyExtension.zip
 *
 * MyExtension/....
 * MyExtension/etc/
 * MyExtension/composer.json
 * MyExtension/....
 * MyExtension/....
 *
 * OR
 *
 * unzip -l MyExtension.zip
 *
 * src/
 * composer.json
 * .
 * .
 *
 * OR
 *
 * unzip -l MyExtension.zip
 *
 * MyExtension/src/
 * MyExtension/composer.json
 * MyExtension/...
 * MyExtension/...
 *
 * The top-level directory name need not match the package name (minus the '.zip' extension)- it
 * is noted in such cases.
 *
 * So whether at top-level, or one level down as illustrated above, it performs the
 * following checks:
 *
 * 1) The supplied file must be a zip file archive.
 *
 * 2) It should contain a valid composer.json file.
 *
 * 3) The commposer.json file is inspected for certain fields - @see validateComposerJson()
 *    below.
 *
 * 4) Based on the package type, additional files are checked to see if it is present - see
 *    the list of globals above for each type which lists the files it checks for existence for
 *    its respective type.
 *
 *    a) $g_magentoModules  (magento2-module)
 *
 *    b) $g_themeFiles  (magento2-theme)
 *
 *    c) $g_langFiles  (magento2-language)
 *
 * 5) For non-metapackages, it checks to see if registration.php is also present.
 *
 * Wherever possible, the check continues on and outputs any errors or warnings to the stderr.
 *
 * @param string  $fname The path to the zip file.
 * @param boolean $debug Debug flag, which if enabled, adds DEBUG lines to the output.
 *
 * @return integer The return code to indicate the validation status:
 *
 *   0 - All checks passed (no errors detected).
 *
 * 101 - Supplied file not in zip format.
 *
 * 102 - Some zip archive error; the erorr message contains the archive error code.
 *
 * 200 - Errors detected during the checks. Specific errors are displayed in stderr.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 *
 */
function validateM2Zip($fname, $debug)
{
    $pkgName = basename($fname);
    $zip = new ZipArchive;
    $res = $zip->open($fname);

    if($res !== true)
        return processZipErrors($pkgName, $res);

    if($debug) {
        displayZipArchive($pkgName, $zip);
    }

    $err = false;

    // Check to see if there is a top-level directory.
    list($topDir, $numDirs) = getTopDir($zip);
    $topDirBasename = basename($topDir);

    if($numDirs > 1) {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": More than one top-level directory detected, " .
            "number of directories = $numDirs. Top level directory expected to be the module directory.\n");
        return 200;
    }

    if($debug) {
        print "DEBUG - \"" . $pkgName . "\": Top level directory - <$topDirBasename>.\n";
    }

    $pkgName2 = basename($fname, '.zip');

    if( ($numDirs ==1) && ($topDirBasename != $pkgName2) ) {
        fwrite(STDERR, "NOTE  - \"" . $pkgName ."\": Top-level directory does not match " .
            "package name - \"$topDirBasename\" != \"$pkgName2\"\n");
    }

    // First check to see if composer.json file is present
    // in JSON format.
    $composerJsonStr = getComposerJson($zip, $topDir);
    $composerJson = '';

    if($composerJsonStr === false) {
        // Is composer.json present anywhere?
        $composerFname = locateFile($zip, 'composer.json');

        if($composerFname === false) {

            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"composer.json\" missing. " .
                "Please consult the \"Name your component\" section of the PHP Developer Guide for more information.\n");
            $err = true;
        }
        else {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"composer.json\" found in unexpected " .
                "place. Zip archive layout not to standard as described in the " .
                "\"Component File Structure\" section of the PHP Developer Guide.\n");
            $err = true;
        }
    }
    else if($composerJsonStr == '') {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Empty \"composer.json\" file detected. " .
            "Please consult the \"Name your component\" section of the PHP Developer Guide for more information.\n");
        $err = true;
    }
    else {
        $composerJson = json_decode($composerJsonStr, true);

        if(is_null($composerJson)) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Bad \"composer.json\" file detected. " .
                "Please consult the \"Name your component\" section of the PHP Developer Guide for more information.\n");
            $err = true;
        }
    }

    if($debug) {
        print "DEBUG - \"" . $pkgName . "\": composer.json\n" . rtrim( str_replace("\n", "\n\t", "\t" . $composerJsonStr), "\t" ) . "\n";
    }

    $type = '';

    // Attempt to find the source folder if it exists
    $srcDir = findSourceFolder($composerJson);
    if($srcDir !== "") {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Alternate folder structure is currently not supported\n");
        $err = true;
    }

    if( is_array($composerJson) )  {
        // Ensure all the needed fields are present in the
        // composer.json.  Based on the type field, ensure
        // its respective files are also present.
        $type = (string) @$composerJson['type'];

        if( !validateComposerJson($pkgName, $composerJson) ) {
            $err = true;
        }

        if( !validateFiles($type, $pkgName, $zip, $topDir, $srcDir) ) {
            $err = true;
        }
    }

    // Check for registration.php - skip it for metapackages.
    if( ($type != '') && ($type != 'metapackage') ) {
        $regPhp = findRegistrationLoad($composerJson);
        $hasRegPhp = false;

        // If we cannot locate "registration.php" based on the composer.json,
        // then we take our best guess as to where it should be.
        if($regPhp === false) {
            $hasRegPhp = registrationPhpExists($zip, $topDir . $srcDir . "registration.php");
        }
        else {
            $hasRegPhp = registrationPhpExists($zip, $topDir . $regPhp);
        }

        if( $hasRegPhp === false ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"registration.php\" is missing. " .
                "Please consult the \"Component Registration\" section of the PHP Developer Guide for more information.\n");
            $err = true;
        }
        elseif( $hasRegPhp <= 0 ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"registration.php\" is empty. " .
                "Please consult the \"Component Registration\" section of the PHP Developer Guide for more information.\n");
            $err = true;
        }
    }

    if($err)
        return 200;

    if($debug) {
        print "DEBUG - \"" . $pkgName . "\": Success, passed all the validation checks.\n";
    }

    return 0;
}

/**
 * Handle zip archive error codes.
 *
 * Helper function to report zip archive errors.
 *
 * @param string $pkgName Name of the zip file.
 * @param integer $res ZipArchive::open() return code.
 *
 * @return integer See codes below.
 *
 */
function processZipErrors($pkgName, $res)
{
    switch($res) {
        case ZipArchive::ER_NOZIP:
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Supplied file not in zip format. " .
                "Please consult the \"Package a component\" section of the PHP Developer Guide for more information.\n");
            return 101;

        default:
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Zip file open failure with code $res.\n");
            return 102;
    }
}

/**
 * Extracts composer.json from the zip archive file.
 *
 * It looks for the composer.json at the top level or under the
 * top level directory if set.
 *
 * @param object $zip ZipArchive object
 * @param string $topDir Top level directory if present.
 *
 * @return string composer.json contents
 *
 */
function getComposerJson($zip, $topDir)
{
    $fname = $topDir . 'composer.json';
    return $zip->getFromName($fname);
}

/**
 * Checks to see if the extension uses an alternate file structure
 *
 * @param array $composerJson
 *
 * @return string filepath of the "source" folder, or empty string if none was found.
 *
 */
function findSourceFolder($composerJson)
{
    $srcFolder = @current(@$composerJson['autoload']['psr-4']);

    if(is_null($srcFolder)) {
        $pathParts = pathinfo(@current(@$composerJson['autoload']['files']));
        $dirname = @$pathParts['dirname'];

        if(is_null($dirname) || ($dirname === '.')) {
            return '';
        }

        return (string) $pathParts['dirname'] . '/';
    }

    return (string) @current(@$composerJson['autoload']['psr-4']);
}

/**
 * Checks to see if registration.php exists in the autoload files section
 *
 * @param array $composerJson
 * @param boolean $isAutoloadSection
 *
 * @return boolean/string Location of registration.php, false otherwise
 *
 */
function findRegistrationLoad($composerJson, $isAutoloadSection = false)
{
    if($isAutoloadSection) {
        $files = @$composerJson['files'];
    }
    else {
        $files = @$composerJson['autoload']['files'];
    }

    if(!is_null($files)) {
        $registrationFile = 'registration.php';
        $registrationFileStringLength = strlen($registrationFile);
        foreach ($files as $file) {
            if (substr($file, -1*$registrationFileStringLength) === $registrationFile) {
                return $file;
            }
        }
    }
    return false;
}

/**
 * Checks to see if registration.php exists in the supplied zip file.
 *
 * It looks for registration.php at the top-level or under the top level if
 * set. It also takes into account the source folder, if it exists.
 * It should be a non-empty file.
 *
 * @param object $zip ZipArchive object
 * @param string $topDir Top level directory if present.
 *
 * @return boolean/integer Size of registration.php, false otherwise.
 *
 */
function registrationPhpExists($zip, $regPhp)
{
    //TODO: Do  'php -l registration.php' to detect syntax errors.

    $stat = $zip->statName($regPhp);

    if($stat === false)
        return false;

    return $stat['size'];
}

/**
 * Validates the composer.json required field and values.
 *
 * It inspects and validates the following required fields:
 *
 * name
 * type
 * version
 * autoload - only for non-metapackages
 * require
 *
 * See comments below for the expected format of the values. Any
 * errors detected here are reported to the stdout.
 *
 * @param string $pkgName Name of the supplied zip file.
 * @param array $composerJson  Json decoded composer.json contents.
 * @return boolean True if all validations succeeded, false otherwise.
 *
 * @see validateComposerAutoload()
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 *
 */
function validateComposerJson($pkgName, $composerJson)
{
    global $g_moduleTypes;

    $name = (string) @$composerJson['name'];
    $type = (string) @$composerJson['type'];
    $version = (string) @$composerJson['version'];
    $autoload = @$composerJson['autoload'];
    $require = @$composerJson['require'];
    $extra = @$composerJson['extra'];
    $res = true;
    $knownType = true;

    // name - must be of the format '<vendor>/<package name>'
    if( !preg_match("/^([a-z0-9_-])+\/([a-z0-9_-])+$/i", $name) ) {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"composer.json\" has invalid name - " .
            "\"$name\". It should be of the format '<vendor>/<package name>'.\n");
        $res = false;
    }

    // type - must be in the known types list.
    if($type == '') {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'type' field in \"composer.json\" is " .
            "missing or empty. The 'type' field is required and can only be one of the following: " .
            "magento2-theme, magento2-language, or magento2-module.\n");
        $res = false;
    }
    else if( !isset($g_moduleTypes[$type]) ) {
        // unknown type
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": Unknown 'type' detected - '$type'. " .
            "The 'type' field is required and can only be one of the following: " .
            "magento2-theme, magento2-language, or magento2-module.\n");
        $res = false;
        $knownType = false;
    }

    // version
    // Expected format as per https://getcomposer.org/doc/04-schema.md#version
    if( !preg_match("/^(|v)([0-9])+\.([0-9])+\.([0-9])+" .
        "(-(patch|p|dev|a|alpha|b|beta|rc)([0-9])*)?$/i", $version)

    ) {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'version' field in \"composer.json\" " .
            "is missing, empty or not in expected format. The 'version' field is required and needs " .
            "to be of the following form as described here: " .
            "https://getcomposer.org/doc/04-schema.md#version .\n");
        $res = false;
    }

    if( $knownType && ($type != 'metapackage') ) {
        // autoload check - not applicable to metapackage
        if(!validateComposerAutoload($type, $pkgName, $autoload)) {
            $res = false;
        }
    }

    if(!validateComposerDependencies($type, $pkgName, $require)) {
        $res = false;
    }

    if( isset($extra) ) {
        // extra['map'] is deprecated and should not be present anymore.
        if( isset($extra['map']) ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The \"extra['map']\" field is deprecated; " .
                "it should not be present anymore.\n");
            $res = false;
        }

        // extra['magento-root-dir'] is deprecated and should not be present anymore.
        if( isset($extra['magento-root-dir']) ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The \"extra['magento-root-dir'] field is deprecated; " .
                "it should not be present anymore.\n");
            $res = false;
        }
    }

    return $res;
}

/**
 * Helper function to validate composer.json autoload field value.
 *
 * It ensures that the following fields are set:
 *
 * files - the list here must contain registration.php
 * psr-4 - ensures that it is a non-empty list with namespace
 *         properly set.
 *
 * Any errors detected here are reported to the stdout.
 *
 * @param string $type The module type from the composer.json 'type' field.
 * @param string $pkgName Name of the supplied zip file.
 * @param array $autoload Map contents of the autoload field from composer.json.
 *
 * @return boolean True if all validations succeeded, false otherwise.
 *
 */
function validateComposerAutoload($type, $pkgName, $autoload)
{
    $res = true;

    if(is_array($autoload)) {
        $files = @$autoload['files'];
        $psr4 = @$autoload['psr-4'];

        if( is_array($files) && (count($files) > 0) ) {
            if(!findRegistrationLoad($autoload, true)) {
                fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"registration.php\" not found in " .
                    "'files' field of the 'autoload' directive. Please consult the \"Component registration\" section of the " .
                    "PHP Developer Guide for more information.\n");
                $res = false;
            }
        }
        else {
            // the 'files' field is what's being referenced.
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'files' field of the 'autoload' " .
                "directive is missing or not set up correctly. Please consult the \"Component registration\" section of the " .
                "PHP Developer Guide for more information.\n");
            $res = false;
        }

        // Currently psr-4 check is only valid for 'magento2-module'.
        //
        //TODO: Can the namespace setting here be actually verified?
        if( ($type == 'magento2-module') && (!is_array($psr4) || (count($psr4) <= 0)) ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'psr4' field of the 'autoload' " .
                "directive is missing or not set up correctly. Please consult the \"Component registration\" section of the " .
                "PHP Developer Guide for more information.\n");
            $res = false;
        }
    }
    else {
        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'autoload' directive is missing or not " .
            "set up correctly. Please consult the \"Component registration\" section of the PHP Developer Guide for more " .
            "information.\n");
        $res = false;
    }

    return $res;
}

/**
 * Helper function to validate composer.json require field value.
 *
 * It ensures that invalid packages and/or dependencies are not specified.
 *
 * Any errors detected here are reported to the stdout.
 *
 * @param string $type The module type from the composer.json 'type' field.
 * @param string $pkgName Name of the supplied zip file.
 * @param array $require List of dependencies specified in require field from composer.json.
 *
 * @return boolean True if all validations succeeded, false otherwise.
 *
 */
function validateComposerDependencies($type, $pkgName, $require)
{
    global $g_invalidDependencies;
    $res = true;

    if(is_array($require) && (count($require) > 0)) {
        foreach($require as $package => $version) {
            if(isset($g_invalidDependencies[$package])) {
                fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The '$package' package must not be specified as a dependency.\n");
                $res = false;
            }
            else if(preg_match("/^magento\//", $package) && ($version === '*')) {
                fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The '$package' must have specific version(s) as a dependency.\n");
                $res = false;
            }
            else if($package === 'php') {
                fwrite(STDERR, "WARNING - \"" . $pkgName . "\": It is not recommended to specify supported php versions in dependencies. " .
                    "If specified, it must at least match the php requirements of the supported Magento versions.\n");
            }

        }
    }
    else if($type == 'metapackage') {
        // metapackages should have non-empty require directive

        fwrite(STDERR, "ERROR - \"" . $pkgName . "\": The 'require' directive in " .
            "\"composer.json\" is missing, empty, or incorrect. Please consult the \"Package a component\" section " .
            "of the PHP Developer Guide for more information.\n");
        $res = false;
    }

    return $res;
}

/**
 * Validate that certain needed files exist for a given package type.
 *
 * It ensures that for a given package type (non-metapackage), its corresponding
 * needed files are present and non-empty. This is a driver function to
 * map the needed files for a given package type. The actual check is done
 * at validateFilesCore()
 *
 * @param string $type The type field from composer.json
 * @param string $pkgName Name of the supplied zip file.
 * @param object $zip The ZipArchive object
 * @param string $topDir The top level directory if present in the zip.
 *
 * @return boolean True if all validations succeeded, false otherwise.
 *
 * @see validateFilesCore()
 *
 */
function validateFiles($type, $pkgName, $zip, $topDir, $srcDir = '')
{
    global $g_magentoModuleFiles;
    global $g_themeFiles;
    global $g_langFiles;


    //TODO: for the various file checks below, right now it does
    //      only existence check (size > 0). It will be nice to
    //      also do additional validation - e.g. etc/module.xml can
    //      be validated to ensure it is correct for magento2-module.

    $res = true;

    switch($type) {
        case 'magento2-module':
            $res = validateFilesCore($type, $pkgName, $g_magentoModuleFiles, $zip, $topDir, $srcDir);
            break;

        case 'magento2-theme':
            $res = validateFilesCore($type, $pkgName, $g_themeFiles, $zip, $topDir);
            break;

        case 'magento2-language':
            $res = validateFilesCore($type, $pkgName, $g_langFiles, $zip, $topDir);
            break;

        case 'metapackage':
        default:   // unknown types are handled earlier in composer validation.
            break;
    }

    return $res;
}

/**
 * Helper function to check existence of non-empty files.
 *
 * The list of files for a given package type is supplied, and
 * for each of them, it checks to see if the file exists and
 * it is not empty in the given zip file.
 *
 * @param string $type The type of package
 * @param string $pkgName Name of the supplied zip file.
 * @param array $files The list of files to check.
 * @param ZipArchive $zip The zip file package to inspect.
 * @param string $topDir The top level directory if present in the zip.
 * @param string $srcDir The source folder if it is present
 *
 * @return boolean True if all the files in the list is present, false otherwise.
 *
 */
function validateFilesCore($type, $pkgName, $files, $zip, $topDir, $srcDir = '')
{
    $res = true;

    foreach($files as $f) {
        $f = $topDir . $srcDir . $f;

        $stat = $zip->statName($f);

        if($stat === false) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"$f\" is missing. " .
                "\"$f\" is a required file for the '$type' type.\n");
            $res = false;
        }
        else if( $stat['size'] <= 0 ) {
            fwrite(STDERR, "ERROR - \"" . $pkgName . "\": \"$f\" is empty. " .
                "\"$f\" is a required file for the '$type' type.\n");
            $res = false;
        }
    }

    return $res;
}

/**
 * Method to extract the top level directory if present in the zip archive.
 *
 * A supplied package can either be archived from the 'main directory'
 * where composer.json, and registration.php (if applicable) exists, or
 * these mandatory files can be one level down, i.e. the zip file has
 * a top level directory. For an example, see comments in validateM2Zip()
 * function above.
 *
 * @param ZipArchive $zip The zip package to inspect.
 *
 * @return array The following values (tuple) are returned in the given offsets:
 *               0 - string Top level directory is present; can be an empty string.
 *               1 - integer Number of top-level directories detected.
 *
 *
 */
function getTopDir($zip)
{
    $topDirs = array();

    for($i = 0; $i < $zip->numFiles; ++$i) {
        $fname =  $zip->getNameIndex($i);
        $pos = strpos($fname, '/');

        if($pos === false) {
            // Any regular files on the top-level implies that there is either no top level directory
            // (i.e. composer.json etc.. is at the top level), or there are spurious files at the top level
            // which is also not expected.
            return array('', 0);
        }

        $topDir = substr($fname, 0, $pos + 1);
        $topDirs[$topDir] = $topDir;
    }

    // Now if there are more that one top level directory, it is not in the expected format.
    $numDirs = count($topDirs);

    if($numDirs != 1) {
        return array('', $numDirs);
    }

    return array( array_shift($topDirs), 1);
}

/**
 * Debug routine to dump the zip archive.
 *
 * Displays the file names and its respective sizes
 * for debugging purposes.
 *
 * @param string $pkgName Name of the supplied zip file.
 * @param ZipArchive $zip The zip file to dump.
 *
 * @return void
 *
 */
function displayZipArchive($pkgName, $zip)
{
    print "DEBUG - \"" . $pkgName . "\": Zip file contents (file and size).\n";
    for($i = 0; $i < $zip->numFiles; ++$i) {
        $fname =  $zip->getNameIndex($i);
        $stat = $zip->statName($fname);
        print "\t$fname - " . $stat['size'] . "\n";
    }
}

/**
 * Locate a file in the zip archive.
 *
 * The supplied file name is checked against all
 * the file path locations in the zip archive at
 * any depth level.
 *
 * @param ZipArchive $zip The zip file to inspect.
 * @param string $fname The file name to check.
 *
 * @return mix  Returns full path to the file if found, or false otherwise.
 *
 */
function locateFile($zip, $fname)
{
    for($i = 0; $i < $zip->numFiles; ++$i) {
        $fname2 =  $zip->getNameIndex($i);

        if($fname == basename($fname2)) {
            return $fname2;
        }
    }

    return false;
}