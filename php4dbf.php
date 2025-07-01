<?php

/**
 * php4dbf Library
 * 
 * This library provides functions to interact with DBF (dBASE) files using PHP.
 * It includes functions for opening DBF files, reading headers and field descriptions,
 * properly padding values, and adding records to DBF files.
 * 
 * Functions:
 * - php4dbf_logline: Logs messages to a log file.
 * - php4dbf_openDbf: Opens a DBF file.
 * - php4dbf_eof: Determines the end of the file (EOF) based on the number of records in the currently open DBF file.
 * - php4dbf_DBUse: Reads all necessary information from a DBF file at once and returns it.
 * - php4dbf_getDbfHeader: Retrieves the header of a DBF file.
 * - php4dbf_getFieldDescriptors: Retrieves the field descriptions of a DBF file.
 * - php4dbf_padValue: Properly pads values based on their type.
 * - php4dbf_addRecordToDbf: Adds records to a DBF file.
 * - php4dbf_calculateRecordLength: Calculates the record length based on field descriptors.
 * - php4dbf_updateRecord: Updates a record in a DBF file.
 * - php4dbf_deleteRecord: Deletes a record in a DBF file.
 * - php4dbf_createDbf: Creates a new DBF file.
 * - php4dbf_compareDbfFiles: Compares two DBF files.
 * - php4dbf_getRecordByIndex: Retrieves a record by its index.
 * - php4dbf_numrecords: Retrieves the number of records in a DBF file.
 * - php4dbf_numfields: Retrieves the number of fields in a DBF file.
 * - php4dbf_get_record_with_names: Retrieves a record with field names from a DBF file.
 * - php4dbf_close: Closes a DBF file.
 * - php4dbf_loadAllFile: Loads the entire DBF file into memory.
 * - php4dbf_loadAllHeader: Extracts header information from the file content string.
 * - php4dbf_readHeader: Reads header information from a file pointer.
 * - php4dbf_extractFields: Extracts field descriptions from the loaded DBF file data.
 * - php4dbf_readRecordByIndex: Reads a record based on the specified index.
 * - php4dbf_closeDbfFile: Closes an open DBF file handle, if present.
 * - php4dbf_openFile: Opens a DBF file and loads either the full content or just the header.
 * - php4dbf_getLastUpdateDate: Extracts the last update date from the DBF header.
 * - php4dbf_flock: Attempts to create a lock and lock file.
 * - php4dbf_phpunlock: Releases the lock created by `flock` using PHP.
 * - php4dbf_unlock: Releases the custom lock by deleting lock files.
 * - php4dbf_updateField: Updates a specific field within a record in a DBF file.
 * - php4dbf_updateLastModifiedDate: Updates the last modified date in the DBF header.
 * - php4dbf_checkFileSizeConsistency: Checks file size consistency with the number of records.
 * - php4dbf_appendBlank: Adds a blank record to a DBF file.
 * - php4dbf_getHeader: Helper function for common header processing.
 * - php4dbf_DBUse: Opens a DBF file and reads the header as well as the field descriptions.
 * - FieldPos: Returns the 1-based position of a field by its name.
 * - FieldName: Returns the name of a field by its 1-based position.
 * - FieldPosLast: Returns the 1-based position of the last field.
 * - php4dbf_dbGoTop: Moves the record pointer to the first valid record.
 * - php4dbf_dbGoTo: Moves the record pointer to a specific record.
 * - php4dbf_init: Initializes the DBF file for reading.
 * - php4dbf_get_next_record: Reads the next record from the DBF file.
 * - php4dbf_parse_record: Parses a record from raw binary data into an associative array.
 * - php4dbf_convert_value: Converts a raw binary value to the appropriate data type based on field type.
 * - php4dbf_findMissingNumbersInRange: Finds missing sequential numbers in a DBF field, optionally within a specified range.
 * - php4dbf_finalize() Finalisiert eine DBF-Datei durch Anfügen von 0x1A (EOF) und optional 0x00.
 * - php4dbf_forceFlushByExec()   Forces a physical flush of a DBF file to disk using an external flush tool
 */

define('PHP4DBF_VERSION', '1.2.0');  // 2025-05-09
 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

/**
 * - php4dbf_dbGoTo: Moves the record pointer to a specific record.
 * - php4dbf_init: Initializes the DBF file for reading.
 * - ...
 */



/**
 * Prüft, ob die aktuelle php4dbf-Version eine Mindestversion erfüllt.
 *
 * @param string $minVersion  Die erforderliche Mindestversion (z. B. "1.2.0")
 * @param bool   $silent      Wenn true, wird keine Ausgabe erzeugt (nur Rückgabewert)
 * @return bool               true, wenn Mindestversion erfüllt, sonst false
 */
function php4dbf_versioncheck_min($minVersion, $silent = false)
{
    if (!defined('PHP4DBF_VERSION')) {
        $msg = "php4dbf-Version nicht definiert!";
        php4dbf_logline("Fehler", $msg, __LINE__);
        if (!$silent) die("❌ $msg");
        return false;
    }

    if (version_compare(PHP4DBF_VERSION, $minVersion, '<')) {
        $msg = "php4dbf-Version zu alt! Erforderlich: $minVersion, gefunden: " . PHP4DBF_VERSION;
        php4dbf_logline("Fehler", $msg, __LINE__);
        if (!$silent) die("❌ $msg");
        return false;
    }

    php4dbf_logline("Info", "php4dbf-Version OK: " . PHP4DBF_VERSION, __LINE__);
    return true;
}








 /**
 * Gibt zurück, ob gelöschte Datensätze aktuell ignoriert werden.
 *
 * Diese Funktion prüft die globale Einstellung `$GLOBALS['php4dbf_deleted']`, 
 * die durch `php4dbf_setDeletedOn()` gesetzt wurde.
 *
 * @return bool true = gelöschte Datensätze werden ignoriert, false = sie werden mitgelesen.
 *
 * @see php4dbf_setDeletedOn()
 */
 

$GLOBALS['php4dbf_deleted'] = true; // Default wie Clipper: gelöscht = ignorieren

function php4dbf_setDeletedOn($value = true) {
    $GLOBALS['php4dbf_deleted'] = $value;
}

function php4dbf_getDeletedOn() {
    return $GLOBALS['php4dbf_deleted'] ?? true;
}



/**
 * Logs a message to a log file.
 *
 * @param string $text The message text.
 * @param string $value An optional value for additional information.
 * @param int $lineNumber The line number where the log message is created.
 * @param bool $loggingEnabled (Optional) Enables or disables logging. Default is true.
 * @param string $filename (Optional) The name of the file where the log occurs.
 * @return void
 */
function php4dbf_logline(string $text, string $value = "", int $lineNumber = 0, bool $loggingEnabled = true, string $filename = ""): void {
    if (!$loggingEnabled) return;
    $callingFile = basename(__FILE__, '.php'); // Removes the .php extension
    $logFile = $callingFile . '.log';
    $logLine = "[" . str_pad((string)$lineNumber, 4, "0", STR_PAD_LEFT) . "] " . str_pad($text, 50) . ": " . $value . " (File: " . basename($filename) . ")\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

// Funktion zum Protokollieren von Arrays
function php4dbf_logArray($label, $array, $lineNumber) {
    php4dbf_logline($label, json_encode($array, JSON_PRETTY_PRINT), $lineNumber);
}


/**
 * Opens a DBF file.
 *
 * @param string $filename Path to the DBF file.
 * @param bool $loggingEnabled (Optional) Enables logging. Default is true.
 * @return resource File pointer of the opened DBF file.
 * @throws Exception If the file cannot be opened.
 */

function php4dbf_openDbf(string $filename, bool $loggingEnabled = true) {
    $dbf = fopen($filename, 'rb+');
    if (!$dbf) {
        php4dbf_logline("Error", "Cannot open DBF file.", __LINE__, $loggingEnabled);
        throw new Exception("Cannot open DBF file.");
    }
    php4dbf_logline("DBF file opened", $filename, __LINE__, $loggingEnabled);
    return $dbf;
}
 

/**
 * Reads the header of a DBF file directly from a file pointer.
 *
 * @param resource $dbf The file pointer of the opened DBF file.
 * @param bool $loggingEnabled (Optional) Enables logging. Default is true.
 * @return array Associative array with the header information.
 * @throws Exception If the header cannot be fully read.
 */
function php4dbf_getDbfHeader($dbf, bool $loggingEnabled = true): array {
    $header = fread($dbf, 32);
    if ($header === false || strlen($header) < 32) {
        php4dbf_logline("Error", "DBF header read error or incomplete header.", __LINE__, $loggingEnabled);
        throw new Exception("DBF header read error or incomplete header.");
    }
    $data = unpack('CfileType/Cyear/Cmonth/Cday/VrecordCount/vheaderLength/vrecordLength', $header);
    php4dbf_logline("DBF header read", json_encode($data), __LINE__, $loggingEnabled);
    return $data;
}

/**
 * Retrieves the field descriptions of a DBF file.
 *
 * @param resource $dbf The file pointer of the opened DBF file.
 * @param bool $loggingEnabled (Optional) Enables logging. Default is true.
 * @return array An array of field descriptions.
 * @throws Exception If there is an error reading the field descriptions.
 */
function php4dbf_getFieldDescriptors($dbf, array $header, bool $loggingEnabled = true): array {
    $fields = [];
    $position = 32; // Start position after the DBF header
    $headerLength = $header['headerLength'];

    fseek($dbf, $position); // Move to the beginning of the field descriptors
    php4dbf_logline("Reading field descriptors from position", (string)$position, __LINE__, $loggingEnabled);

    while ($position < $headerLength) {
        $field = fread($dbf, 32);
        if ($field === false || strlen($field) < 32) {
            php4dbf_logline("Error", "Error reading field descriptor.", __LINE__, $loggingEnabled);
            break;
        }

        // Check for header termination
        $firstByte = ord($field[0]);
        if ($firstByte == 0x0D || $firstByte == 0x1A) {
            php4dbf_logline("End of field descriptors", "Position: $position", __LINE__, $loggingEnabled);
            break;
        }

        $name = trim(str_replace("\0", '', substr($field, 0, 11)));
        $type = $field[11];
        $fieldLength   = ord($field[16]);
        $fieldDecimals = ord($field[17]);

        // Logging für ungewöhnliche Typen
        if (!in_array($type, ['C', 'N', 'D', 'L', 'M'])) {
            php4dbf_logline("⚠️ Unerwarteter Feldtyp", "Typ: $type bei Feld: $name", __LINE__, $loggingEnabled);
        }

        // 'F' wie 'N' behandeln
        if ($type === 'F') {
            php4dbf_logline("Info", "Feldtyp F wird wie N behandelt: $name", __LINE__, $loggingEnabled);
            $type = 'N';
        }

        if (empty($name) && empty($type) && $fieldLength === 0) {
            php4dbf_logline("Empty field descriptor found, stopping.", "", __LINE__, $loggingEnabled);
            break;
        }

        $fields[] = [
            'name'     => $name,
            'type'     => $type,
            'length'   => $fieldLength,
            'decimals' => $fieldDecimals
        ];

        $position += 32;
    }

    php4dbf_logline("Field descriptors read", json_encode($fields), __LINE__, $loggingEnabled);
    return $fields;
}

/**
 * Liest einen Datensatz aus einer DBF-Datei anhand des gegebenen Index.
 *
 * Diese Funktion liest einen einzelnen Datensatz aus der übergebenen DBF-Datei (über Datei-Handle).
 * Standardmäßig werden Datensätze, die als gelöscht markiert sind (erkennbar an 0x2A als erstes Byte), ignoriert.
 * Das Verhalten kann global über `php4dbf_setDeletedOn(false)` geändert werden, um auch gelöschte Datensätze zu laden.
 *
 * @param resource $fileHandle Ein geöffneter Datei-Handle zur DBF-Datei (z. B. von fopen).
 * @param int $index Nullbasierter Index des zu lesenden Datensatzes.
 * @param array $header Header-Informationen der DBF-Datei (z. B. von php4dbf_getDbfHeader()).
 * @param array $fields Array der Feldbeschreibungen (z. B. von php4dbf_getFieldDescriptors()).
 * @param bool $loggingEnabled Optional: Aktiviert Fehlermeldungen per error_log(). Standard: true.
 *
 * @return array|null Gibt ein assoziatives Array mit Feldnamen und Werten zurück,
 *                    oder null, wenn der Datensatz als gelöscht markiert ist (je nach Einstellung),
 *                    oder false bei einem Lese-Fehler.
 *
 * @see php4dbf_setDeletedOn() Zum Umschalten der Behandlung gelöschter Datensätze
 * @see php4dbf_getDeletedOn() Gibt den aktuellen Status der DELETE-Filterung zurück
 */

 

function php4dbf_getRecordByIndex($fileHandle, $index, $header, $fields, $loggingEnabled = true) {
    $recordOffset = $header['headerLength'] + ($index * $header['recordLength']);
    fseek($fileHandle, $recordOffset);
    $record = fread($fileHandle, $header['recordLength']);

    if ($record === false) {
        if ($loggingEnabled) {
            error_log("Error reading record at index $index");
        }
        return null;
    }

    // 🟡 Optional: Gelöschte Datensätze überspringen (Clipper: SET DELETED ON)
    if (php4dbf_getDeletedOn() && ord($record[0]) === 0x2A) {
        return null;
    }

    $result = [];
    $fieldOffset = 1; // Skipping the deleted flag byte
    foreach ($fields as $field) {
        $fieldValue = trim(substr($record, $fieldOffset, $field['length']));
        $result[$field['name']] = my_utf8_encode($fieldValue); // Ensure UTF-8 encoding
        $fieldOffset += $field['length'];
    }

    // 🟢 Wenn DELETED OFF, aber der Datensatz ist gelöscht, markieren wir das optional:
    if (ord($record[0]) === 0x2A && !php4dbf_getDeletedOn()) {
        $result['_deleted'] = true;
    }

    return $result;
}


/**
 * Properly pads a value based on its type.
 *
 * @param string $value The value to pad.
 * @param int $length The desired length.
 * @param string $type The field's data type.
 * @param bool $loggingEnabled (Optional) Enables logging. Default is true.
 * @return string The properly padded value.
 */
function php4dbf_padValue(string $value, int $length, string $type, bool $loggingEnabled = true): string {
    if ($loggingEnabled) {
        php4dbf_logline("Pad Value Start", "Type: $type", __LINE__);
        php4dbf_logline("Original Value", $value, __LINE__);
        php4dbf_logline("Length", (string)$length, __LINE__);
    }

    switch (strtoupper($type)) {
        case 'C': // Character
            $trimmedValue = substr($value, 0, $length);
            $paddedValue = str_pad($trimmedValue, $length);
            break;

        case 'N': // Numeric
        case 'F': // Floating point → wie Numeric
            $trimmedValue = substr($value, 0, $length);
            $paddedValue = str_pad($trimmedValue, $length, '0', STR_PAD_LEFT);
            break;

        case 'L': // Logical
            $paddedValue = ($value && strtoupper($value) !== 'F') ? 'T' : 'F';
            break;

        case 'D': // Date YYYYMMDD
            $dateValue = str_replace('-', '', $value);
            $paddedValue = (strlen($dateValue) === 8 && ctype_digit($dateValue))
                ? $dateValue
                : str_pad('', $length);
            break;

        default: // Standard fallback
            $trimmedValue = substr($value, 0, $length);
            $paddedValue = str_pad($trimmedValue, $length);
            break;
    }

    if ($loggingEnabled) {
        php4dbf_logline("Padded Value", $paddedValue, __LINE__);
    }

    return $paddedValue;
}

 
/**
 * Calculates the length of a record based on field descriptions.
 *
 * @param array $fields An array of field descriptions.
 * @return int The calculated record length.
 */
function php4dbf_calculateRecordLength(array $fields): int {
    $length = 1; // Start with 1 for the deletion flag
    foreach ($fields as $field) {
        $length += $field['length'];
    }
    return $length;
}

/**
 * Adds a new record to a DBF file.
 * 
 * This function opens a DBF file, constructs a new record based on the provided associative array, 
 * and appends it to the file. The number of records in the DBF header is updated accordingly,
 * and the last modification date in the DBF file is also updated to the current date.
 * 
 * @param string $filename The path to the DBF file.
 * @param array $record An associative array where the keys are the field names and the values are the data to add.
 * @param bool $loggingEnabled (Optional) Enables logging. Default is true.
 * @return bool Returns true if the record was successfully added.
 * 
 * @throws Exception If there is an error opening the file or writing the record.
 */
function php4dbf_addRecordToDbf(string $filename, array $input, bool $loggingEnabled = true): bool {
    php4dbf_logArray("recived array", $record, __LINE__);
    // DBF-Daten abrufen
    $dbfArray = php4dbf_DBUse($filename, $loggingEnabled);
    $dbf = $dbfArray['fileHandle'];
    $fields = $dbfArray['fields'];
    $recordLength = $dbfArray['recordLength'];
    $header = $dbfArray['header'];

    // Konvertiere JSON-Schlüssel in Uppercase
    $record = [];
    foreach ($input as $key => $value) {
        $record[strtoupper($key)] = $value;
    }

    if ($loggingEnabled) {
        php4dbf_logline("Converted Record to Uppercase", json_encode($record, JSON_PRETTY_PRINT), __LINE__);
    }

    // Konstruiere den Datensatz
    $recordData = ' ';
    foreach ($fields as $field) {
        $value = isset($record[$field['name']]) ? $record[$field['name']] : '';
        $paddedValue = php4dbf_padValue($value, $field['length'], $field['type'], $loggingEnabled);
        $recordData .= $paddedValue;

        // Logge das gepolsterte Feld
        if ($loggingEnabled) {
            php4dbf_logline("Field Processed", "Field: {$field['name']}, Value: $value, Padded: $paddedValue", __LINE__);
        }
    }

    // Schreibe den Datensatz in die DBF-Datei
    $recordCount = $header['recordCount'];
    $newRecordNumber = $recordCount + 1;
    $headerLength = $header['headerLength'];
    $insertPosition = $headerLength + ($recordCount * $recordLength);
    fseek($dbf, $insertPosition);
    fwrite($dbf, $recordData);

    // Aktualisiere die Anzahl der Datensätze
    fseek($dbf, 4);
    fwrite($dbf, pack('V', $newRecordNumber));

    // Aktualisiere das Datum der letzten Änderung
    php4dbf_updateLastModifiedDate($dbf, $loggingEnabled);

    fclose($dbf);

    if ($loggingEnabled) {
        php4dbf_logline("Record Added Successfully", json_encode($record), __LINE__);
    }

    return true;
}



/**
 * Aktualisiert einen Datensatz in einer DBF-Datei.
 *
 * @param string $filename Der Pfad zur DBF-Datei.
 * @param int $index Der Null-basierte Index des zu aktualisierenden Datensatzes.
 * @param array $data Ein assoziatives Array mit den neuen Daten.
 * @param bool $loggingEnabled (Optional) Aktiviert das Logging. Standard ist true.
 * @return bool Gibt true zurück, wenn der Datensatz erfolgreich aktualisiert wurde.
 * @throws Exception Wenn der Datensatzindex ungültig ist oder ein Fehler beim Lesen/Schreiben auftritt.
 */
 


function php4dbf_updateRecord(string $filename, int $index, array $data, bool $loggingEnabled = true): bool {
    $DBData = php4dbf_DBUse($filename, $loggingEnabled);
    $dbf = $DBData['fileHandle'];
    $header = $DBData['header'];
    $fields = $DBData['fields'];
    $recordLength = $DBData['recordLength'];
    $recordCount = $DBData['recordCount'];

    if ($index < 0 || $index >= $recordCount) {
        php4dbf_logline("Invalid record index", (string)$index, __LINE__, $loggingEnabled);
        fclose($dbf);
        throw new Exception("Invalid record index.");
    }

    $recordOffset = $header['headerLength'] + ($index * $recordLength);
    fseek($dbf, $recordOffset);
    $record = fread($dbf, $recordLength);
    if ($record === false) {
        php4dbf_logline("Failed to read record", (string)$index, __LINE__, $loggingEnabled);
        fclose($dbf);
        throw new Exception("Failed to read record.");
    }

    if (ord($record[0]) == 0x2A) { // 0x2A = gelöscht markierter Datensatz
        php4dbf_logline("Record is marked as deleted", (string)$index, __LINE__, $loggingEnabled);
        fclose($dbf);
        return false;
    }

    // Protokolliere die ursprünglichen Datensatzdaten
  //  php4dbf_logline("Original record data", bin2hex($record), __LINE__, $loggingEnabled);

    // Aktualisiere den Datensatz mit neuen Daten
    $updatedRecord = ' ';
    $offset = 1;
    foreach ($fields as $field) {
        if (isset($data[$field['name']])) {
            $value = php4dbf_padValue($data[$field['name']], $field['length'], $field['type'], $loggingEnabled);
            $updatedRecord .= $value;
            php4dbf_logline("Updated field", json_encode(['name' => $field['name'], 'value' => $value]), __LINE__, $loggingEnabled);
        } else {
            $value = substr($record, $offset, $field['length']);
            $updatedRecord .= $value;
        }
        $offset += $field['length'];
    }

    // Protokolliere die aktualisierten Datensatzdaten
  //  php4dbf_logline("Updated record data", bin2hex($updatedRecord), __LINE__, $loggingEnabled);

    fseek($dbf, $recordOffset);
    fwrite($dbf, $updatedRecord);

    // Aktualisiere das letzte Änderungsdatum
    php4dbf_updateLastModifiedDate($dbf, $loggingEnabled);

    fclose($dbf);

    php4dbf_logline("Record updated successfully", (string)$index, __LINE__, $loggingEnabled);

    return true;
}


function php4dbf_createDbf($filename, $definition, $loggingEnabled = true) {
    if (file_exists($filename)) {
        unlink($filename); // Delete existing file
        php4dbf_logline("Deleted existing file", $filename, __LINE__, $loggingEnabled);
    }

    $file = fopen($filename, 'wb');
    if (!$file) {
        php4dbf_logline("Error", "Cannot create DBF file.", __LINE__, $loggingEnabled);
        throw new Exception("Cannot create DBF file.");
    }

    // Header length = 32 (fixed) + 32 * numFields + 1 (field descriptor terminator)
    $headerLength = 32 + (count($definition) * 32) + 1;
    php4dbf_logline("Calculated header length", $headerLength, __LINE__, $loggingEnabled);

    // Write DBF file header
    fwrite($file, pack("C4", 3, date('Y') - 1900, date('n'), date('j'))); // Version & date
    fwrite($file, pack("V", 0)); // Number of records (0 initially)
    fwrite($file, pack("v", $headerLength)); // Header length
    $recordLength = 1 + array_sum(array_column($definition, 'length')); // 1 for deletion flag
    fwrite($file, pack("v", $recordLength)); // Record length
    fwrite($file, str_repeat("\0", 20)); // Reserved bytes

    php4dbf_logline("Header written", "", __LINE__, $loggingEnabled);

    // Write each field descriptor
    foreach ($definition as $field) {
        $fieldName = str_pad(substr($field['name'], 0, 11), 11, "\0"); // max 11 chars
        $fieldType = strtoupper($field['type']);
        $fieldLength = $field['length'];
        $decimalCount = isset($field['decimals']) ? $field['decimals'] : 0;

        php4dbf_logline("Writing field descriptor", json_encode([
            'name'     => $fieldName,
            'type'     => $fieldType,
            'length'   => $fieldLength,
            'decimals' => $decimalCount
        ]), __LINE__, $loggingEnabled);

        fwrite($file, $fieldName);              // Field name (11 bytes)
        fwrite($file, $fieldType);              // Field type (1 byte)
        fwrite($file, str_repeat("\0", 4));     // Field data address (not used)
        fwrite($file, pack("C", $fieldLength)); // Field length (1 byte)
        fwrite($file, pack("C", $decimalCount));// Decimal count (1 byte)
        fwrite($file, str_repeat("\0", 14));    // Reserved bytes
    }

    // Terminator for field descriptor block
    fwrite($file, chr(0x0D));

    fclose($file); // close after header

    // 🔁 Append EOF (0x1A) + optional 0x00
    $file = fopen($filename, 'ab');
    if ($file) {
        fwrite($file, chr(0x1A)); // EOF
        fwrite($file, chr(0x00)); // optional
        fclose($file);
        php4dbf_logline("Appended EOF (0x1A) and optional null byte to DBF", $filename, __LINE__);
    } else {
        php4dbf_logline("Warning", "Could not reopen DBF to append EOF.", __LINE__);
    }

    php4dbf_logline("DBF file created", $filename, __LINE__, $loggingEnabled);
    php4dbf_logline("Expected header length", $headerLength, __LINE__, $loggingEnabled);
    php4dbf_logline("Actual file size", filesize($filename), __LINE__, $loggingEnabled);

    return true;
}

 
function php4dbf_compareDbfFiles($filename1, $filename2, $loggingEnabled = true) {
    php4dbf_logline("Comparing DBF files", "", __LINE__, $filename1);
    $dbf1 = fopen($filename1, 'rb');
    $dbf2 = fopen($filename2, 'rb');
    if (!$dbf1 || !$dbf2) {
        php4dbf_logline("Comparing DBF files", "", __LINE__, true, $filename1);
        throw new Exception("Cannot open one of the DBF files.");
    }

    $header1 = php4dbf_getDbfHeader($dbf1, $loggingEnabled);
    $header2 = php4dbf_getDbfHeader($dbf2, $loggingEnabled);
    $fields1 = php4dbf_getFieldDescriptors($dbf1, $loggingEnabled);
    $fields2 = php4dbf_getFieldDescriptors($dbf2, $loggingEnabled);

    php4dbf_logline("Comparison results", json_encode(['header1' => $header1, 'header2' => $header2, 'fields1' => $fields1, 'fields2' => $fields2]), __LINE__, $filename1);

    // Check for 0x0D and 0x1A bytes in both files
    fseek($dbf1, -2, SEEK_END);
    $terminator1 = fread($dbf1, 2);
    fseek($dbf2, -2, SEEK_END);
    $terminator2 = fread($dbf2, 2);
    php4dbf_logline("Terminator byte check", json_encode(['filename1' => bin2hex($terminator1), 'filename2' => bin2hex($terminator2)]), __LINE__, $filename1);

    fclose($dbf1);
    fclose($dbf2);
}

 
function php4dbf_numrecords($dbf_id) {
    fseek($dbf_id, 4, SEEK_SET);
    $recordCountData = fread($dbf_id, 4);
    $recordCount = unpack('V', $recordCountData)[1];
    return $recordCount;
}

 
function php4dbf_numfields($dbf_id) {
    fseek($dbf_id, 8, SEEK_SET);
    $headerLengthData = fread($dbf_id, 2);
    $headerLength = unpack('v', $headerLengthData)[1];
    return ($headerLength - 32) / 32;
}

 
function php4dbf_get_record_with_names($dbf_id, $record_number) {
    fseek($dbf_id, 8, SEEK_SET);
    $headerLengthData = fread($dbf_id, 2);
    $headerLength = unpack('v', $headerLengthData)[1];

    fseek($dbf_id, 10, SEEK_SET);
    $recordLengthData = fread($dbf_id, 2);
    $recordLength = unpack('v', $recordLengthData)[1];

    fseek($dbf_id, $headerLength + ($recordLength * ($record_number - 1)), SEEK_SET);
    $recordData = fread($dbf_id, $recordLength);

    $fields = [];
    $offset = 1;
    fseek($dbf_id, 32, SEEK_SET);
    while (ftell($dbf_id) < $headerLength) {
        $fieldData = fread($dbf_id, 32);
        $field = [];
        $field['name'] = rtrim(substr($fieldData, 0, 11));
        $field['type'] = $fieldData[11];
        $field['length'] = ord($fieldData[16]);
        $fields[] = $field;
    }

    $record = [];
    foreach ($fields as $field) {
        $record[$field['name']] = my_utf8_encode(rtrim(substr($recordData, $offset, $field['length']))); // Ensure UTF-8 encoding
        $offset += $field['length'];
    }

    return $record;
}


function php4dbf_close($dbf) {
    return fclose($dbf);
}

function my_utf8_encode($string) {
    $output = '';
    $length = strlen($string);

    for ($i = 0; $i < $length; $i++) {
        $char = ord($string[$i]);
        if ($char < 128) {
            $output .= chr($char);
        } elseif ($char < 2048) {
            $output .= chr(192 + (($char - ($char % 64)) / 64));
            $output .= chr(128 + ($char % 64));
        } else {
            $output .= chr(224 + (($char - ($char % 4096)) / 4096));
            $output .= chr(128 + ((($char % 4096) - ($char % 64)) / 64));
            $output .= chr(128 + ($char % 64));
        }
    }

    return $output;
}

/**
 * Loads the entire DBF file into memory.
 * 
 * @param string $filename Path to the DBF file.
 * @return string The entire content of the file.
 */
function php4dbf_loadAllFile($filename) {
    $fileHandle = fopen($filename, 'rb');
    if (!$fileHandle) {
        throw new Exception("Cannot open DBF file.");
    }
    
    $fileData = stream_get_contents($fileHandle);
    fclose($fileHandle);
    
    return $fileData;
}

/**
 * Extracts the header information from the file data.
 * Quelle: Nimmt den gesamten Dateiinhalts-String ($fileData) als Parameter und extrahiert den Header aus den ersten 32 Bytes.
 * Keine Fehlerbehandlung: Diese Funktion enthält keine Logging- oder Fehlerbehandlungslogik.
 * 
 * @param string $fileData The entire content of the file.
 * @return array An associative array containing the header information.
 */
function php4dbf_loadAllHeader($fileData) {
    $header = substr($fileData, 0, 32);
    $data = unpack('CfileType/Cyear/Cmonth/Cday/VrecordCount/vheaderLength/vrecordLength', $header);
    return $data;
}

/**
 * Reads the header information from the file pointer.
 * 
 * Quelle: Sehr ähnlich wie php4dbf_getDbfHeader, liest den Header aus einem Dateizeiger ($filePointer).
* Keine Logging: Diese Funktion enthält keine Logging-Funktionalität, bietet aber dieselbe Fehlerbehandlung wie die erste Funktion.

 * @param resource $filePointer The file pointer to the open DBF file.
 * @return array An associative array containing the header information.
 */
function php4dbf_readHeader($filePointer) {
    $header = fread($filePointer, 32);
    if ($header === false || strlen($header) < 32) {
        throw new Exception("DBF header read error or incomplete header.");
    }
    $data = unpack('CfileType/Cyear/Cmonth/Cday/VrecordCount/vheaderLength/vrecordLength', $header);
    return $data;
}


/**
   * Extrahiert die Feldbeschreibungen aus einem geladenen DBF-Dateiinhalts-Array.
   *
   * @param string $fileData Der gesamte Dateiinhalt der DBF-Datei.
   * @param int $headerLength Die Länge des Headers in der DBF-Datei.
   * @return array Ein Array von Feldbeschreibungen, das die Namen, Typen und Längen der Felder enthält.
   */

// Inkorrekte Nutzung von php4dbf_extractFields():

  function php4dbf_extractFields($fileData, $headerLength) {
    $fields = [];
    $offset = 32;  // Die Felder beginnen direkt nach den ersten 32 Bytes des Headers
    $offsetrecord = 1;

    while ($offset < $headerLength) {
        $field = substr($fileData, $offset, 32);
        if ($field[0] == chr(0x0D)) {
            break;  // Ende der Felderbeschreibungen
        }
        $fieldName = rtrim(substr($field, 0, 11));
        $fieldType = $field[11];
        $fieldLength = ord($field[16]);
        
        if (preg_match('/^[a-zA-Z0-9_]+$/', $fieldName) && in_array($fieldType, ['C', 'N', 'L', 'D', 'M'])) {
            $fields[] = ['name' => $fieldName, 'type' => $fieldType, 'length' => $fieldLength, 'offset' => $offsetrecord];
        }
        $offset += 32;
        // Calculate and assign offsetrecord
        $offsetrecord +=  $fieldLength; // Increment offsetrecord by the length of the field


    }
    

    php4dbf_logline("Keys:", json_encode( $fields ), __LINE__, true );



    return $fields;
}



/**
 * Liest einen Datensatz aus einer DBF-Datei basierend auf dem angegebenen Index.
 * Diese Funktion unterstützt sowohl das Lesen aus einem in den Speicher geladenen DBF-Dateiinhalts-Array 
 * als auch aus einer Datei über einen Datei-Handle und loggt Fehler optional.
 *
 * @param array $data Ein Array, das die notwendigen Daten enthält, einschließlich 'fileData' (für im Speicher gehaltene Daten) 
 *                    oder 'filePointer' (für Datei-Handle), 'headerLength', 'recordLength', 'recordCount', und 'fields'.
 * @param int $index Der Index des Datensatzes, der gelesen werden soll. Beginnt bei 0.
 * @param bool $loggingEnabled (Optional) Aktiviert oder deaktiviert das Logging von Fehlern beim Lesen. Standard ist true.
 * @return string|array|null|false Gibt den Datensatz als String oder als Array zurück, wenn er erfolgreich gelesen wurde.
 *                                 Gibt null zurück, wenn der Datensatz als gelöscht markiert ist.
 *                                 Gibt false zurück, wenn der Index ungültig ist.
 */

function php4dbf_readRecordByIndex($data, $index) {
    if ($data['fileData']) {
        if ($index < 0 || $index >= $data['recordCount']) {
            return false;
        }
        $recordOffset = $data['headerLength'] + ($index * $data['recordLength']);
        $record = substr($data['fileData'], $recordOffset, $data['recordLength']);
        if (!$record || ord($record[0]) == 0x2A) {
            return null;
        }
        return $record;
    } else {
        if ($index < 0 || $index >= $data['recordCount']) {
            return false;
        }
        $recordOffset = $data['headerLength'] + ($index * $data['recordLength']);
        fseek($data['filePointer'], $recordOffset);
        $record = fread($data['filePointer'], $data['recordLength']);
        if (!$record || ord($record[0]) == 0x2A) {
            return null;
        }
        return $record;
    }
}

/**
 * Schließt einen geöffneten DBF-Datei-Handle, falls vorhanden.
 *
 * Diese Funktion schließt den Datei-Handle, der in 'filePointer' im übergebenen Datenarray enthalten ist,
 * um sicherzustellen, dass keine offenen Datei-Handles verbleiben.
 *
 * @param array $data Ein Array, das einen Datei-Handle in 'filePointer' enthält, der geschlossen werden soll.
 * @return void
 */
function php4dbf_closeDbfFile($data) {
    if (isset($data['filePointer']) && $data['filePointer']) {
        fclose($data['filePointer']);
    }
}


/**
 * Öffnet eine DBF-Datei und lädt entweder den gesamten Inhalt oder nur den Header, basierend auf der übergebenen Option.
 * 
 * Diese Funktion unterstützt das Laden des gesamten Datei-Inhalts in den Speicher oder das Laden nur des Headers.
 * Optional können zu filternde Felder übergeben werden, die für die weitere Verarbeitung verwendet werden.
 * 
 * @param string $filename Der Pfad zur DBF-Datei.
 * @param bool $loadEntireFile Ob die gesamte Datei in den Speicher geladen werden soll (true) oder nur der Header (false).
 * @param array $filterFields (Optional) Ein Array von Feldern, die für die Filterung verwendet werden sollen. Standard ist ein leeres Array.
 * @return array Ein assoziatives Array mit allen notwendigen Informationen zur DBF-Datei.
 * @throws Exception Wenn ein Fehler beim Lesen der Datei auftritt.
 */
function php4dbf_openFile($filename, $loadEntireFile, $filterFields = []) {
    if ($loadEntireFile) {
        php4dbf_logline("Load the entire file into memory");
        $fileData = php4dbf_loadAllFile($filename);
        if ($fileData === false) {
            throw new Exception("Error reading the DBF file.");
        }
        $header = php4dbf_loadAllHeader($fileData);
        
        // Letztes Änderungsdatum aus dem Header extrahieren
        $lastUpdateDate = php4dbf_getLastUpdateDate($header);
        
        return [
            'fileData' => $fileData,
            'recordCount' => $header['recordCount'],
            'headerLength' => $header['headerLength'],
            'recordLength' => $header['recordLength'],
            'fields' => php4dbf_extractFields( $fileData, $header['headerLength']),
            'filePointer' => null,
            'filterFields' => $filterFields,  // Verwende die übergebenen Filterfelder
            'lastUpdateDate' => $lastUpdateDate  // Füge das letzte Änderungsdatum hinzu
        ];
    } else {
        php4dbf_logline("Read only the header from the file");
        $filePointer = php4dbf_openDbf($filename);
        $header = php4dbf_readHeader($filePointer);
        
        // Letztes Änderungsdatum aus dem Header extrahieren
        $lastUpdateDate = php4dbf_getLastUpdateDate($header);
        
        return [
            'fileData' => null,
            'recordCount' => $header['recordCount'],
            'headerLength' => $header['headerLength'],
            'recordLength' => $header['recordLength'],
            'fields' => php4dbf_getFieldDescriptors($filePointer),
            'filePointer' => $filePointer,
            'filterFields' => $filterFields,  // Verwende die übergebenen Filterfelder
            'lastUpdateDate' => $lastUpdateDate  // Füge das letzte Änderungsdatum hinzu
        ];
    }
}


function php4dbf_getLastUpdateDate($header) {
    $year = $header['year'] + 1900;
    $month = $header['month'];
    $day = $header['day'];

    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}



/**
 * Versucht, eine Protector- und Lockdatei zu erstellen.
 * Gibt true zurück, wenn erfolgreich gesperrt wurde, false bei Fehlschlag.
 */
function php4dbf_flock($lock_filename, $protector_filename) {
    php4dbf_logline('Attempting to acquire protector lock', $protector_filename, __LINE__);

    // Überprüfen, ob die Protector-Datei existiert
    if (file_exists($protector_filename)) {
        php4dbf_logline('Protector file already exists, cannot lock', $protector_filename, __LINE__);
        return false; // Protector-Datei existiert bereits
    }

    // Protector-Datei erstellen
    $protector_file = fopen($protector_filename, 'w');
    if ($protector_file) {
        fwrite($protector_file, "Protector file active");
        fclose($protector_file);
        php4dbf_logline('Protector lock acquired successfully', $protector_filename, __LINE__);
    } else {
        php4dbf_logline('Failed to create protector file', $protector_filename, __LINE__);
        return false; // Fehler beim Erstellen der Protector-Datei
    }

    
    // Überprüfen, ob die Lock-Datei existiert
    php4dbf_logline('Attempting to acquire lock', $lock_filename, __LINE__);
    if (file_exists($lock_filename)) {
        php4dbf_logline('Lock file already exists, cannot lock', $lock_filename, __LINE__);
        // Lösche die Protector-Datei, da das Programm hier abbricht
        unlink($protector_filename);
        php4dbf_logline('Protector file deleted due to lock file existence', $protector_filename, __LINE__);
        return false; // Lock-Datei existiert bereits
    }

    // Lock-Datei erstellen
    $lock_file = fopen($lock_filename, 'w');
    if ($lock_file) {
        fwrite($lock_file, "Lock file active");
        fclose($lock_file);
        php4dbf_logline('Lock acquired successfully', $lock_filename, __LINE__);
        return true; // Lock erfolgreich
    } else {
        php4dbf_logline('Failed to create lock file', $lock_filename, __LINE__);
        // Lösche die Protector-Datei, da das Programm hier abbricht
        unlink($protector_filename);
        php4dbf_logline('Protector file deleted due to lock creation failure', $protector_filename, __LINE__);
        return false; // Fehler beim Erstellen der Lock-Datei
    }
}

/**
 * Hebt die Sperre auf, indem die Sperrdateien gelöscht werden.
 */
function php4dbf_unlock($lock_filename, $protector_filename) {
    php4dbf_logline('Attempting to release lock', $lock_filename, __LINE__);

    if (file_exists($lock_filename)) {
        unlink($lock_filename);
        php4dbf_logline('Lock file released successfully', $lock_filename, __LINE__);
    } else {
        php4dbf_logline('Lock file does not exist, nothing to unlock', $lock_filename, __LINE__);
    }

    php4dbf_logline('Attempting to release protector lock', $protector_filename, __LINE__);
    if (file_exists($protector_filename)) {
        unlink($protector_filename);
        php4dbf_logline('Protector file released successfully', $protector_filename, __LINE__);
    } else {
        php4dbf_logline('Protector file does not exist, nothing to unlock', $protector_filename, __LINE__);
    }
}


/**
* Description: Updates a specific field within a record in a DBF file.
* 
* Parameters:
* 
* $filename: The path to the DBF file.
* $index: The zero-based index of the record to be updated.
* $fieldName: The name of the field to update. This name will be automatically converted to uppercase.
* $newValue: The new value to set in the specified field.
* $loggingEnabled: (Optional) Boolean flag to enable or disable logging. Default is true.
* Returns:
* 
* true: If the field was successfully updated.
* null: If the record is marked as deleted.
* Exception: If the field or record is not found, or if an error occurs.
*/

//In php4dbf_updateField() lesen Sie die gesamte Datei mit file_get_contents(), um die Felder zu extrahieren. Dies ist ineffizient. 
//Verwenden Sie stattdessen php4dbf_getFieldDescriptors(), um direkt aus dem Datei-Handle die Feldbeschreibungen zu erhalten.

function php4dbf_updateField($filename, $index, $fieldName, $newValue, $loggingEnabled = true) {
    $dbf = php4dbf_openDbf($filename, $loggingEnabled);
    $header = php4dbf_getDbfHeader($dbf, $loggingEnabled);
    $fields = php4dbf_extractFields(file_get_contents($filename), $header['headerLength']);
    $recordLength = php4dbf_calculateRecordLength($fields);

    $recordCount = $header['recordCount'];
    if ($index < 0 || $index >= $recordCount) {
        php4dbf_logline("Invalid record index", $index, __LINE__, $loggingEnabled);
        throw new Exception("Invalid record index.");
    }

    // Normalize the input field name to uppercase
    $fieldName = strtoupper($fieldName);

    $recordOffset = $header['headerLength'] + ($index * $recordLength);
    fseek($dbf, $recordOffset);
    $record = fread($dbf, $recordLength);
    if (!$record) {
        php4dbf_logline("Failed to read record", $index, __LINE__, $loggingEnabled);
        throw new Exception("Failed to read record.");
    }

    if (ord($record[0]) == 0x2A) { // 0x2A = deleted record
        php4dbf_logline("Record is marked as deleted", $index, __LINE__, $loggingEnabled);
        return null;
    }

    // Locate the field in the record by its name
    $fieldFound = false;
    foreach ($fields as $field) {
        if ($field['name'] == $fieldName) {
            $fieldFound = true;
            $offset = $field['offset'];
            $paddedValue = php4dbf_padValue($newValue, $field['length'], $field['type'], $loggingEnabled);

            // Replace the old field value with the new one
            $updatedRecord = substr_replace($record, $paddedValue, $offset, $field['length']);

            // Write the updated record back to the file
            fseek($dbf, $recordOffset);
            fwrite($dbf, $updatedRecord);

            // Update the last modification date using the new function
            php4dbf_updateLastModifiedDate($dbf, $loggingEnabled);
            break;
        }
    }

    fclose($dbf);

    if ($fieldFound) {
        php4dbf_logline("Field updated successfully", $fieldName, __LINE__, $loggingEnabled);
        return true;
    } else {
        php4dbf_logline("Field not found", $fieldName, __LINE__, $loggingEnabled);
        throw new Exception("Field not found.");
    }
}


function php4dbf_updateLastModifiedDate($dbf, $loggingEnabled = true) {
    // Get the current date
    $currentDate = getdate();
    
    // Move the file pointer to the last update date position in the header (bytes 1-3)
    fseek($dbf, 1);
    
    // Write the current year, month, and day
    fwrite($dbf, pack('C', $currentDate['year'] - 1900)); // Year offset from 1900
    fwrite($dbf, pack('C', $currentDate['mon'])); // Month
    fwrite($dbf, pack('C', $currentDate['mday'])); // Day
    
    if ($loggingEnabled) {
        php4dbf_logline("Last modification date updated", sprintf('%04d-%02d-%02d', $currentDate['year'], $currentDate['mon'], $currentDate['mday']), __LINE__);
    }
}


function php4dbf_checkFileSizeConsistency($filename, $loggingEnabled = true) {
    // Open the DBF file
    $dbf = php4dbf_openDbf($filename, $loggingEnabled);
    
    // Retrieve the header information
    $header = php4dbf_getDbfHeader($dbf, $loggingEnabled);

    // Calculate the expected file size
    $headerLength = $header['headerLength'];
    $recordLength = $header['recordLength'];
    $recordCount = $header['recordCount'];
    
    $expectedFileSize = $headerLength + ($recordLength * $recordCount) + 1; // +1 for the file terminator byte (0x1A)
    
    // Close the DBF file
    fclose($dbf);
    
    // Get the actual file size
    $actualFileSize = filesize($filename);

    // Log the results
    php4dbf_logline("Expected file size", $expectedFileSize, __LINE__, $loggingEnabled);
    php4dbf_logline("Actual file size", $actualFileSize, __LINE__, $loggingEnabled);
    
    // Check if the file sizes match
    if ($actualFileSize === $expectedFileSize) {
        php4dbf_logline("File size is consistent with the number of records.", '', __LINE__, $loggingEnabled);
        return true;
    } else {
        php4dbf_logline("File size mismatch: inconsistent with the number of records.", '', __LINE__, $loggingEnabled);
        return false;
    }
}


/**
 * Appends a blank record to a DBF file, inserting default values based on field types.
 * 
 * This function opens a DBF file, constructs a new blank record using default values for each field type,
 * and appends it to the file. The number of records in the DBF header is updated accordingly,
 * and the last modification date in the DBF file is also updated to the current date.
 * 
 * @param string $filename The path to the DBF file.
 * @param bool $loggingEnabled (Optional) Enables or disables logging. Default is true.
 * 
 * @return bool Returns true if the blank record was successfully added.
 * 
 * @throws Exception If there is an error opening the file or writing the record.
 */
function php4dbf_appendBlank($filename, $loggingEnabled = true) {
    $dbf = php4dbf_openDbf($filename, $loggingEnabled);
    $header = php4dbf_getDbfHeader($dbf, $loggingEnabled);
    $fields = php4dbf_getFieldDescriptors($dbf, $loggingEnabled);
    $recordLength = php4dbf_calculateRecordLength($fields);

    // Construct record data with default values based on field type
    $recordData = ' '; // Start with a space for the deletion flag
    foreach ($fields as $field) {
        // Set default values based on field type
        switch ($field['type']) {
            case 'C': // Character type
                $paddedValue = str_repeat(' ', $field['length']); // Fill with spaces
                break;
            case 'N': // Numeric type
                $paddedValue = str_pad('0', $field['length'], '0', STR_PAD_LEFT); // Fill with zeroes
                break;
            case 'L': // Logical type
                $paddedValue = 'F'; // Default to false
                break;
            case 'D': // Date type
                $paddedValue = '        '; // 8 spaces for empty date (or optionally "  .  .    ")
                break;
            case 'M': // Memo type
                $paddedValue = str_pad('', $field['length']); // Empty memo
                break;
            default:
                $paddedValue = str_repeat(' ', $field['length']); // Default to spaces for unknown types
        }
        $recordData .= $paddedValue;
    }

    // Ensure the record data matches the record length
    if (strlen($recordData) < $recordLength) {
        $recordData = str_pad($recordData, $recordLength);
    } else {
        $recordData = substr($recordData, 0, $recordLength);
    }

    // Determine new record position
    $recordCount = $header['recordCount'];
    $newRecordNumber = $recordCount + 1;

    $headerLength = $header['headerLength'];
    $insertPosition = $headerLength + $recordCount * $recordLength;
    fseek($dbf, $insertPosition);
    fwrite($dbf, $recordData);

    // Update the record count in the header
    fseek($dbf, 4);
    fwrite($dbf, pack('V', $newRecordNumber));

    // Update the last modification date using the new function
    php4dbf_updateLastModifiedDate($dbf, $loggingEnabled);

    fclose($dbf);
    return true;
}

/**
 * Vorschlag zur Bereinigung:
    * Du könntest die Funktionen zusammenfassen, um Redundanzen zu vermeiden. Die Hauptunterscheidung liegt darin, 
    * ob der Header aus einem Dateizeiger oder aus einem Dateistring gelesen wird. 
    * Daher könntest du eine einzelne Funktion schreiben, die mit beiden Formaten arbeiten kann, und optional Logging sowie Fehlerbehandlung bereitstellen.
    * Bereinigte Version:
 * 
  
 */

/**
 * Ruft den Header einer DBF-Datei ab.
 *
 * @param resource|string $source Entweder ein Dateizeiger oder der gesamte Dateiinhalts-String.
 * @param bool $isFilePointer Gibt an, ob die Quelle ein Dateizeiger ist. Standard ist true.
 * @param bool $loggingEnabled (Optional) Aktiviert das Logging. Standard ist true.
 * @return array Assoziatives Array mit den Headerinformationen.
 * @throws Exception Wenn der Header nicht vollständig gelesen werden kann.
 */
function php4dbf_getHeader($source, bool $isFilePointer = true, bool $loggingEnabled = true): array {
    if ($isFilePointer) {
        // Quelle ist ein Dateizeiger, also verwenden wir fread
        $header = fread($source, 32);
    } else {
        // Quelle ist der gesamte Dateistring, verwenden wir substr
        $header = substr($source, 0, 32);
    }

    // Fehlerbehandlung bei ungültigem Header
    if ($header === false || strlen($header) < 32) {
        if ($loggingEnabled) {
            php4dbf_logline("Error", "DBF header read error or incomplete header.", __LINE__, $loggingEnabled);
        }
        throw new Exception("DBF header read error or incomplete header.");
    }

    // Entpacken des Headers
    $data = unpack('CfileType/Cyear/Cmonth/Cday/VrecordCount/vheaderLength/vrecordLength', $header);

    // Optionales Logging
    if ($loggingEnabled) {
        php4dbf_logline("DBF header read", json_encode($data), __LINE__, $loggingEnabled);
    }

    return $data;
} 

/**
 * php4dbf_DBUse
 *
 * Öffnet eine DBF-Datei, liest den Header, die Anzahl der Datensätze und die Feldbeschreibungen,
 * und gibt alle Informationen in einem assoziativen Array zurück.
 *
 * @param string $filename - Pfad zur DBF-Datei.
 * @param bool $loggingEnabled - Optional, aktiviert das Logging (Standard: true).
 *
 * @return array - Ein assoziatives Array mit Dateihandle, Anzahl der Datensätze, Headerlänge, Datensatzlänge und Feldbeschreibungen.
 *
 * @throws Exception - Wird ausgelöst, wenn die Datei nicht geöffnet werden kann.
 *
 * Beispiel:
 * $dbfArray = php4dbf_DBUse('datafile.dbf');
 * $recordCount = $dbfArray['recordCount']; // Anzahl der Datensätze
 */

function php4dbf_DBUse($filename, $loggingEnabled = true) {
    $dbf = php4dbf_openDbf($filename, $loggingEnabled);
    $header = php4dbf_getDbfHeader($dbf, $loggingEnabled);
    $fields = php4dbf_getFieldDescriptors($dbf, $header, $loggingEnabled);

    $dbfArray = [
        'fileHandle' => $dbf,
        'recordCount' => $header['recordCount'],
        'headerLength' => $header['headerLength'],
        'recordLength' => $header['recordLength'],
        'fields' => $fields,
        'header' => $header
    ];

    return $dbfArray;
}





/**
 * FieldPos
 * Gibt die 1-basierte Position eines Feldes anhand seines Namens zurück.
 *
 * @param array $DBData - Das von php4dbf_DBUse zurückgegebene Array.
 * @param string $fieldName - Der Name des gesuchten Feldes.
 * @return int|null - Die 1-basierte Position des Feldes oder null, falls nicht gefunden.
 */
function FieldPos($DBData, $fieldName) {
    foreach ($DBData['fields'] as $index => $field) {
        if ($field['name'] === $fieldName) {
            return $index + 1; // Position in menschlicher Zählung (1-basiert)
        }
    }
    return null; // Falls das Feld nicht gefunden wird
}

/**
 * FieldName
 * Gibt den Namen eines Feldes anhand seiner 1-basierten Position zurück.
 *
 * @param array $DBData - Das von php4dbf_DBUse zurückgegebene Array.
 * @param int $nField - Die 1-basierte Position des Feldes.
 * @return string|null - Der Name des Feldes oder null, falls die Position ungültig ist.
 */
function FieldName($DBData, $nField) {
    if (isset($DBData['fields'][$nField - 1])) {
        return $DBData['fields'][$nField - 1]['name'];
    }
    return null; // Falls die Position ungültig ist
}

/**
 * FieldPosLast
 * Gibt die 1-basierte Position des letzten Feldes zurück.
 *
 * @param array $DBData - Das von php4dbf_DBUse zurückgegebene Array.
 * @return int - Die Position des letzten Feldes.
 */
function FieldPosLast($DBData) {
    return count($DBData['fields']); // Position des letzten Feldes
}

/**
 * php4dbf_dbGoTop
 * 
 * Moves the record pointer to the first valid record in the DBF file (top of the database).
 * This function moves the file pointer to the first record position after the header.
 *
 * @param array $DBData An associative array that holds all the necessary database information,
 *                      including the file handle, header length, and record length.
 * @return bool Returns true on success, or false on failure.
 */
function php4dbf_dbGoTop($DBData) {
    // Ensure we have a valid DBData array with necessary information
    if (!isset($DBData['fileHandle']) || !isset($DBData['headerLength']) || !isset($DBData['recordLength'])) {
        return false;
    }
    
    // Move the file pointer to the top (just after the header)
    $firstRecordOffset = $DBData['headerLength']; // Top of the file is just after the header
    $result = fseek($DBData['fileHandle'], $firstRecordOffset);
    
    // Check if the seek operation was successful
    return $result === 0;
}

/**
 * php4dbf_dbGoTo
 * 
 * Moves the record pointer to a specific record position in the DBF file.
 * This function calculates the byte offset based on the header length and record length,
 * and sets the file pointer to the desired record's position.
 *
 * @param array $DBData An associative array containing the necessary database information,
 *                      such as the file handle, header length, and record length.
 * @param int $start The record number to move the file pointer to. The numbering starts from 1.
 * @return int Returns 0 on success, or -1 on failure, as per the fseek() function's return value.
 */

function php4dbf_dbGoTo($DBData, $start) {
    // Calculate the offset for the desired record based on the header length and record length
    $firstRecordOffset = $DBData['headerLength'] + (($start - 1) * $DBData['recordLength']);

    // Move the file pointer to the calculated offset
    $result = fseek($DBData['fileHandle'], $firstRecordOffset);

    return $result;
}



/**
 * neu ab 15-09-2024
 * 
 * 
 */

/**
 * Initializes the DBF file for reading.
 *
 * @param string $filename Path to the DBF file.
 * @return resource Returns the file handle of the opened DBF file.
 * @throws Exception If the file cannot be opened.
 */



//In php4dbf_init() und php4dbf_get_next_record() verwenden Sie statische Variablen, die jedoch nur innerhalb der jeweiligen Funktion gültig sind. Um Daten zwischen diesen Funktionen zu teilen,
// sollten Sie eine Klasse verwenden oder die Variablen als globale Variablen deklarieren.
function php4dbf_init($filename)
{
    static $fileHandle = null;
    static $header = null;
    static $fields = null;
    static $recordLength = 0;

    if ($fileHandle === null) {
        $fileHandle = fopen($filename, 'rb');
        if (!$fileHandle) {
            throw new Exception("Cannot open DBF file.");
        }

        $header = php4dbf_getDbfHeader($fileHandle);
        $fields = php4dbf_getFieldDescriptors($fileHandle);
        $recordLength = $header['recordLength'];

        // Move file pointer to the first record
        fseek($fileHandle, $header['headerLength']);
    }

    return $fileHandle;
}



/**
 * Reads the next record from the DBF file.
 *
 * @return array|false Returns an associative array with the record's fields and values, or false if no more records are available.
 * @throws Exception If the DBF file has not been initialized.
 */
//Uninitialisierte Variablen in php4dbf_get_next_record():

//Die Variablen $totalRecords und $currentIndex werden nicht initialisiert. Stellen Sie sicher, dass $totalRecords mit der tatsächlichen Anzahl der Datensätze aus dem Header initialisiert wird und $currentIndex korrekt inkrementiert wird.


function php4dbf_get_next_record()
{
    static $fileHandle = null;
    static $recordLength = 0;
    static $header = null;
    static $fields = null;
    static $totalRecords = 0;
    static $currentIndex = 0;

    if ($fileHandle === null) {
        throw new Exception("DBF file not initialized. Call php4dbf_init() first.");
    }

    if ($currentIndex >= $totalRecords) {
        return false; // No more records
    }

    $recordData = fread($fileHandle, $recordLength);
    if ($recordData === false || strlen($recordData) < $recordLength) {
        return false; // End of file or read error
    }

    $currentIndex++;
    return php4dbf_parse_record($recordData, $fields);
}



/**
 * Parses a record from raw binary data into an associative array.
 *
 * @param string $recordData The raw binary data of the DBF record.
 * @param array $fields The list of field descriptors for the DBF file.
 * @return array Returns an associative array with the record's fields and values.
 */
function php4dbf_parse_record($recordData, $fields)
{
    $record = [];
    $offset = 1; // Skip deletion flag

    foreach ($fields as $field) {
        $rawValue = substr($recordData, $offset, $field['length']);
        $value = php4dbf_convert_value($rawValue, $field['type']);
        $record[$field['name']] = $value;
        $offset += $field['length'];
    }

    return $record;
}

/**
 * Converts a raw binary value to the appropriate data type based on field type.
 *
 * @param string $rawValue The raw value to be converted.
 * @param string $type The field type (e.g., 'N', 'L', 'D').
 * @return mixed Returns the converted value (string, float, boolean, or DateTime).
 */
function php4dbf_convert_value($rawValue, $type)
{
    $value = trim($rawValue);
    switch (strtoupper($type)) {
        case 'N':
            return (float)$value;
        case 'L':
            return strtoupper($value) === 'T';
        case 'D':
            return $value ? DateTime::createFromFormat('Ymd', $value)->format('Y-m-d') : null;
        default:
            return $value;
    }
}



/**
 * Locks a file for exclusive access using PHP's flock mechanism.
 *
 * @param string $filename Path to the file to be locked.
 * @param string $mode The mode to open the file (default is 'rb+').
 * @return resource Returns the file handle of the locked file.
 * @throws Exception If the file cannot be opened or locked.
 */
function php4dbf_phpflock($filename, $mode = 'rb+')
{
    $fileHandle = fopen($filename, $mode);
    if (!$fileHandle) {
        throw new Exception("Cannot open file for locking.");
    }

    if (flock($fileHandle, LOCK_EX)) {
        // File is locked for exclusive access
        return $fileHandle;
    } else {
        fclose($fileHandle);
        throw new Exception("Unable to lock file.");
    }
}

/**
 * Unlocks a file and closes the file handle.
 *
 * @param resource $fileHandle The file handle of the locked file.
 * @return void
 */
function php4dbf_phpunlock($fileHandle)
{
    flock($fileHandle, LOCK_UN);
    fclose($fileHandle);
}




/**
 * Example usage of the DBF functions for reading records one at a time.
 *
 * @throws Exception If an error occurs during file operations.
 
* try {
*     php4dbf_init('path/to/your.dbf');
* 
*     while (($record = php4dbf_get_next_record()) !== false) {
*         if ($record !== null) {
*             // Process your record here
*             print_r($record);
*         }
*     }
* } catch (Exception $e) {
*     echo "Error: " . $e->getMessage();
* }
*/


/**
 * Determines the end of the file (EOF) based on the number of records in the currently open DBF file.
 *
 * @param array $dbData The array containing DBF data, including the number of records.
 * @return int The last valid record index.
 * @throws Exception If no DBF file is open or if dbData is invalid.
 */
function php4dbf_eof($dbData) {
    // Check if dbData is valid and a file is open
    if (!isset($dbData['recordCount'])) {
        throw new Exception("No DBF file is open or dbData is invalid.");
    }

    // Return the total number of records
    return $dbData['recordCount'];
}


/**
 * Findet fehlende fortlaufende Nummern in einem DBF-Feld, optional im vorgegebenen Bereich.
 *
 * @param string $dbfpathname Pfad zur DBF-Datei
 * @param string $field2check Name des zu überprüfenden Feldes
 * @param int|null $start Optionaler Startwert der Reihe (inklusive)
 * @param int|null $end Optionaler Endwert der Reihe (inklusive)
 * @return array [
 *     'missing' => array der fehlenden Nummern (int),
 *     'range'   => [start, end] tatsächlich geprüfter Bereich
 * ]
 */
function php4dbf_findMissingNumbersInRange(string $dbfpathname, string $field2check, ?int $start = null, ?int $end = null): array {
    $dbf = php4dbf_DBUse($dbfpathname, true);
    $recordCount = $dbf['recordCount'];
    $field = strtoupper($field2check);
    $nummern = [];

    for ($i = 0; $i < $recordCount; $i++) {
        $ds = php4dbf_getRecordByIndex($dbf['fileHandle'], $i, $dbf['header'], $dbf['fields']);
        if ($ds && isset($ds[$field])) {
            $roh = trim($ds[$field]);
            if (is_numeric($roh)) {
                $nummern[] = (int)$roh;
            }
        }
    }

    fclose($dbf['fileHandle']);

    if (empty($nummern)) {
        return ['missing' => [], 'range' => null];
    }

    $nummern = array_unique($nummern);
    sort($nummern);

    $min = min($nummern);
    $max = max($nummern);

    $startWert = $start ?? $min;
    $endWert   = $end   ?? $max;

    $vollständig = range($startWert, $endWert);
    $fehlende = array_values(array_diff($vollständig, $nummern));

    return [
        'missing' => $fehlende,
        'range'   => [$startWert, $endWert]
    ];
}



/**
 * Finalisiert eine DBF-Datei durch Anfügen von 0x1A (EOF) und optional 0x00.
 * Sollte aufgerufen werden, nachdem alle Datensätze geschrieben wurden.
 *
 * @param string $filename Pfad zur DBF-Datei.
 * @param bool $loggingEnabled Logging aktivieren.
 * @return void
 */
function php4dbf_finalize(string $filename, bool $loggingEnabled = true): void {
    $eofMarker = chr(0x1A);
    $nullByte  = chr(0x00);

    $fileSize = filesize($filename);
    $file = fopen($filename, 'rb+');

    if (!$file) {
        php4dbf_logline("Finalize", "Konnte Datei nicht öffnen: $filename", __LINE__, $loggingEnabled);
        return;
    }

    // Letztes Byte prüfen
    fseek($file, -2, SEEK_END);
    $lastBytes = fread($file, 2);

    if ($lastBytes !== $eofMarker . $nullByte) {
        php4dbf_logline("Finalize", "EOF wird angehängt an: $filename", __LINE__, $loggingEnabled);
        fclose($file);

        $append = fopen($filename, 'ab');
        if ($append) {
            fwrite($append, $eofMarker);
            fwrite($append, $nullByte); // optional
            fclose($append);
        }
    } else {
        php4dbf_logline("Finalize", "EOF bereits vorhanden in: $filename", __LINE__, $loggingEnabled);
        fclose($file);
    }
}


/**
 * Forces a physical flush of a DBF file to disk using an external flush tool.
 *
 * This function executes a system-level `flush.exe` command that calls the Windows API `FlushFileBuffers`
 * to ensure that all buffered write operations for the specified file are physically written to the disk.
 *
 * This is necessary because PHP's native `fwrite()` and `fclose()` do not guarantee a hardware-level flush,
 * especially when running on Windows, on network drives, USB devices, or systems using aggressive write caching.
 *
 * Example use case: After a DBF record is added or updated, calling this function ensures data is flushed
 * beyond the OS file buffer, minimizing the risk of data loss due to power failure or sudden disconnection.
 *
 * Note: This function assumes that `flush.exe` is available in the system PATH or application directory.
 * The executable must return `0` on success, or a non-zero exit code on failure.
 *
 * @param string $filename         The full path to the DBF file to flush (e.g., 'C:\\data\\kunden.dbf').
 * @param bool   $loggingEnabled   (Optional) Whether to log the operation result. Default: true.
 * @return bool                    Returns true if flush succeeded (exit code 0), false otherwise.
 *
 * @see https://learn.microsoft.com/en-us/windows/win32/api/fileapi/nf-fileapi-flushfilebuffers
 */


function php4dbf_forceFlushByExec(string $filename, bool $loggingEnabled = true): bool {
    $escaped = escapeshellarg($filename);
    $output = null;
    $returnCode = 0;

    exec("flush.exe $escaped", $output, $returnCode);

    if ($loggingEnabled) {
        php4dbf_logline("Exec flush", "ReturnCode=$returnCode Output=" . implode("; ", $output), __LINE__);
    }

    return $returnCode === 0;
}



