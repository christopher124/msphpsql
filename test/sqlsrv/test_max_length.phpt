--TEST--
Maximum length outputs from stored procs for string types (nvarchar, varchar, and varbinary)
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php

set_time_limit(0);

$inValue1 = str_repeat( "A", 3999 );

$outValue1 = "TEST";

sqlsrv_configure('WarningsReturnAsErrors', 0);  //  True
sqlsrv_configure('LogSubsystems', 15);  //  True

require( 'MsCommon.inc' );

$conn = Connect();

$field_type = 'NVARCHAR(4000)';

$stmt = sqlsrv_query($conn, "DROP PROC [TestFullLenStringsOut]");
$stmt = sqlsrv_query($conn, "CREATE PROC [TestFullLenStringsOut] (@p1 " . $field_type . ", @p2 " . $field_type . " OUTPUT)
 AS
 BEGIN
   SELECT @p2 = CONVERT(" . $field_type . ", @p1 + N'A')
 END");
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}

// remember to increment buffer_len at stmt.cpp:1358 to 8001 to see what happens and 
// verify that something is sent by ODBC to the server in the profiler
$stmt = sqlsrv_query($conn, "{CALL [TestFullLenStringsOut] (?, ?)}", 
      array(
        array($inValue1, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING('utf-8'), SQLSRV_SQLTYPE_NVARCHAR(4000)),
        array(&$outValue1, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING('utf-8'), SQLSRV_SQLTYPE_NVARCHAR(4000))));
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}
while( sqlsrv_next_result( $stmt )) {}

if ( strlen( $outValue1 ) != 4000 )
    echo "Length of returned value unexpected!\n";
$str = substr( $outValue1, -2, 2 );
if ($str != 'AA')
    echo "Returned substring $str invalid!\n";

$field_type = 'VARCHAR(8000)';
$inValue1 = str_repeat( "A", 7999 );

$stmt = sqlsrv_query($conn, "DROP PROC [TestFullLenStringsOut]");
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}
$stmt = sqlsrv_query( $conn, "CREATE PROC [TestFullLenStringsOut] (@p1 " . $field_type . ", @p2 " . $field_type . " OUTPUT)
 AS
 BEGIN
   SELECT @p2 = CONVERT(" . $field_type . ", @p1 + 'A')
 END" );
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}

$stmt = sqlsrv_query($conn, "{CALL [TestFullLenStringsOut] (?, ?)}", 
     array(
       array($inValue1, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(8000)),
       array(&$outValue1, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_CHAR), SQLSRV_SQLTYPE_VARCHAR(8000))));
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}
while( sqlsrv_next_result( $stmt )) {}

if ( strlen( $outValue1 ) != 8000 )
    echo "Length of returned value unexpected!\n";
$str = substr( $outValue1, -2, 2 );
if ($str != 'AA')
    echo "Returned substring $str invalid!\n";

$field_type = 'VARBINARY(8000)';
$inValue1 = str_repeat( "A", 7999 );

$stmt = sqlsrv_query($conn, "DROP PROC [TestFullLenStringsOut]");
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}
$stmt = sqlsrv_query($conn, "CREATE PROC [TestFullLenStringsOut] (@p1 " . $field_type . ", @p2 " . $field_type . " OUTPUT)
 AS
 BEGIN
   SELECT @p2 = CONVERT(" . $field_type . ", @p1 + 0x42)
 END");
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}

$stmt = sqlsrv_query($conn, "{CALL [TestFullLenStringsOut] (?, ?)}", 
     array(
       array($inValue1, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY(8000)),
       array(&$outValue1, SQLSRV_PARAM_INOUT, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY(8000))));
if( $stmt === false ) {
    die( print_r( sqlsrv_errors(), true ));
}
while( sqlsrv_next_result( $stmt )) {}

if ( strlen( $outValue1 ) != 8000 )
    echo "Length of returned value unexpected!\n";
$str = substr( $outValue1, -2, 2 );
if ($str != 'AB')
    echo "Returned substring $str invalid!\n";

echo "Test complete.\n";

sqlsrv_free_stmt( $stmt );
sqlsrv_close( $conn );  //  True

?>
--EXPECT--
Test complete.