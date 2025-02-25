<?php

namespace PhpXmlRpc;

use PhpXmlRpc\Helper\Logger;
use PhpXmlRpc\Helper\XMLParser;

/**
 * A helper class to easily convert between Value objects and php native values.
 *
 * @todo implement an interface
 * @todo add class constants for the options values
 */
class Encoder
{
    protected static $logger;
    protected static $parser;

    public function getLogger()
    {
        if (self::$logger === null) {
            self::$logger = Logger::instance();
        }
        return self::$logger;
    }

    /**
     * @param $logger
     * @return void
     */
    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    public function getParser()
    {
        if (self::$parser === null) {
            self::$parser = new XMLParser();
        }
        return self::$parser;
    }

    /**
     * @param $parser
     * @return void
     */
    public static function setParser($parser)
    {
        self::$parser = $parser;
    }

    /**
     * Takes an xmlrpc Value in object instance and translates it into native PHP types, recursively.
     * Works with xmlrpc Request objects as input, too.
     * Xmlrpc dateTime values will be converted to strings or DateTime objects depending on an $options parameter
     * Supports i8 and NIL xmlrpc values without the need for specific options.
     * Both xmlrpc arrays and structs are decoded into PHP arrays, with the exception described below:
     * Given proper options parameter, can rebuild generic php object instances (provided those have been encoded to
     * xmlrpc format using a corresponding option in php_xmlrpc_encode()).
     * PLEASE NOTE that rebuilding php objects involves calling their constructor function.
     * This means that the remote communication end can decide which php code will get executed on your server, leaving
     * the door possibly open to 'php-injection' style of attacks (provided you have some classes defined on your server
     * that might wreak havoc if instances are built outside an appropriate context).
     * Make sure you trust the remote server/client before enabling this!
     *
     * @author Dan Libby (dan@libby.com)
     *
     * @param Value|Request $xmlrpcVal
     * @param array $options accepted elements:
     *                      - 'decode_php_objs': if set in the options array, xmlrpc structs can be decoded into php
     *                         objects, see the details above;
     *                      - 'dates_as_objects': when set xmlrpc dateTimes are decoded as php DateTime objects
     *                      - 'extension_api': reserved for usage by phpxmlrpc-polyfill
     * @return mixed
     *
     * Feature creep -- add an option to allow converting xmlrpc dateTime values to unix timestamps (integers)
     */
    public function decode($xmlrpcVal, $options = array())
    {
        switch ($xmlrpcVal->kindOf()) {
            case 'scalar':
                if (in_array('extension_api', $options)) {
                    $val = reset($xmlrpcVal->me);
                    $typ = key($xmlrpcVal->me);
                    switch ($typ) {
                        case 'dateTime.iso8601':
                            $xmlrpcVal = array(
                                'xmlrpc_type' => 'datetime',
                                'scalar' => $val,
                                'timestamp' => \PhpXmlRpc\Helper\Date::iso8601Decode($val)
                            );
                            return (object)$xmlrpcVal;
                        case 'base64':
                            $xmlrpcVal = array(
                                'xmlrpc_type' => 'base64',
                                'scalar' => $val
                            );
                            return (object)$xmlrpcVal;
                        case 'string':
                            if (isset($options['extension_api_encoding'])) {
                                $dval = @iconv('UTF-8', $options['extension_api_encoding'], $val);
                                if ($dval !== false) {
                                    return $dval;
                                }
                            }
                            //return $val;
                            // break through voluntarily
                        default:
                            return $val;
                    }
                }
                if (in_array('dates_as_objects', $options) && $xmlrpcVal->scalartyp() == 'dateTime.iso8601') {
                    // we return a Datetime object instead of a string since now the constructor of xmlrpc value accepts
                    // safely string, int and DateTimeInterface, we cater to all 3 cases here
                    $out = $xmlrpcVal->scalarval();
                    if (is_string($out)) {
                        $out = strtotime($out);
                    }
                    if (is_int($out)) {
                        $result = new \DateTime();
                        $result->setTimestamp($out);

                        return $result;
                    } elseif (is_a($out, 'DateTimeInterface') || is_a($out, 'DateTime')) {
                        return $out;
                    }
                }
                return $xmlrpcVal->scalarval();

            case 'array':
                $arr = array();
                foreach ($xmlrpcVal as $value) {
                    $arr[] = $this->decode($value, $options);
                }
                return $arr;

            case 'struct':
                // If user said so, try to rebuild php objects for specific struct vals.
                /// @todo should we raise a warning for class not found?
                // shall we check for proper subclass of xmlrpc value instead of presence of _php_class to detect
                // what we can do?
                if (in_array('decode_php_objs', $options) && $xmlrpcVal->_php_class != ''
                    && class_exists($xmlrpcVal->_php_class)
                ) {
                    $obj = @new $xmlrpcVal->_php_class();
                    foreach ($xmlrpcVal as $key => $value) {
                        $obj->$key = $this->decode($value, $options);
                    }
                    return $obj;
                } else {
                    $arr = array();
                    foreach ($xmlrpcVal as $key => $value) {
                        $arr[$key] = $this->decode($value, $options);
                    }
                    return $arr;
                }

            case 'msg':
                $paramCount = $xmlrpcVal->getNumParams();
                $arr = array();
                for ($i = 0; $i < $paramCount; $i++) {
                    $arr[] = $this->decode($xmlrpcVal->getParam($i), $options);
                }
                return $arr;

            /// @todo throw on unsupported type
        }
    }

    /**
     * Takes native php types and encodes them into xmlrpc Value objects, recursively.
     * PHP strings, integers, floats and booleans have a straightforward encoding - note that integers will _not_ be
     * converted to xmlrpc <i8> elements, even if they exceed the 32-bit range.
     * PHP arrays will be encoded to either xmlrpc structs or arrays, depending on whether they are hashes
     * or plain 0..N integer indexed.
     * PHP objects will be encoded into xmlrpc structs, except if they implement DateTimeInterface, in which case they
     * will be encoded as dateTime values.
     * PhpXmlRpc\Value objects will not be double-encoded - which makes it possible to pass in a pre-created base64 Value
     * as part of a php array.
     * If given a proper $options parameter, php object instances will be encoded into 'special' xmlrpc values, that can
     * later be decoded into php object instances by calling php_xmlrpc_decode() with a corresponding option.
     * PHP resource and NULL variables will be converted into uninitialized Value objects (which will lead to invalid
     * xmlrpc when later serialized); to support encoding of the latter use the appropriate $options parameter.
     *
     * @author Dan Libby (dan@libby.com)
     *
     * @param mixed $phpVal the value to be converted into an xmlrpc value object
     * @param array $options can include:
     *                       - 'encode_php_objs' when set, some out-of-band info will be added to the xml produced by
     *                         serializing the built Value, which can later be decoced by this library to rebuild an
     *                         instance of the same php object
     *                       - 'auto_dates': when set, any string which respects the xmlrpc datetime format will be converted to a dateTime Value
     *                       - 'null_extension': when set, php NULL values will be converted to an xmlrpc <NIL> (or <EX:NIL>) Value
     *                       - 'extension_api': reserved for usage by phpxmlrpc-polyfill
     * @return Value
     *
     * Feature creep -- could support more types via optional type argument (string => datetime support has been added,
     * ??? => base64 not yet). Also: allow auto-encoding of integers to i8 when too-big to fit into i4
     */
    public function encode($phpVal, $options = array())
    {
        $type = gettype($phpVal);
        switch ($type) {
            case 'string':
                /// @todo should we be stricter in the accepted dates (ie. reject more of invalid days & times)?
                if (in_array('auto_dates', $options) && preg_match('/^[0-9]{8}T[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $phpVal)) {
                    $xmlrpcVal = new Value($phpVal, Value::$xmlrpcDateTime);
                } else {
                    $xmlrpcVal = new Value($phpVal, Value::$xmlrpcString);
                }
                break;
            case 'integer':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcInt);
                break;
            case 'double':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcDouble);
                break;
            case 'boolean':
                $xmlrpcVal = new Value($phpVal, Value::$xmlrpcBoolean);
                break;
            case 'array':
                // A shorter one-liner would be
                //     $tmp = array_diff(array_keys($phpVal), range(0, count($phpVal)-1));
                // but execution time skyrockets!
                $j = 0;
                $arr = array();
                $ko = false;
                foreach ($phpVal as $key => $val) {
                    $arr[$key] = $this->encode($val, $options);
                    if (!$ko && $key !== $j) {
                        $ko = true;
                    }
                    $j++;
                }
                if ($ko) {
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcStruct);
                } else {
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcArray);
                }
                break;
            case 'object':
                if (is_a($phpVal, 'PhpXmlRpc\Value')) {
                    $xmlrpcVal = $phpVal;
                // DateTimeInterface is not present in php 5.4...
                } elseif (is_a($phpVal, 'DateTimeInterface') || is_a($phpVal, 'DateTime')) {
                    $xmlrpcVal = new Value($phpVal->format('Ymd\TH:i:s'), Value::$xmlrpcDateTime);
                } elseif (in_array('extension_api', $options) && $phpVal instanceof \stdClass && isset($phpVal->xmlrpc_type)) {
                    // Handle the 'pre-converted' base64 and datetime values
                    if (isset($phpVal->scalar)) {
                        switch ($phpVal->xmlrpc_type) {
                            case 'base64':
                                $xmlrpcVal = new Value($phpVal->scalar, Value::$xmlrpcBase64);
                                break;
                            case 'datetime':
                                $xmlrpcVal = new Value($phpVal->scalar, Value::$xmlrpcDateTime);
                                break;
                            default:
                                $xmlrpcVal = new Value();
                        }
                    } else {
                        $xmlrpcVal = new Value();
                    }

                } else {
                    $arr = array();
                    foreach ($phpVal as $k => $v) {
                        $arr[$k] = $this->encode($v, $options);
                    }
                    $xmlrpcVal = new Value($arr, Value::$xmlrpcStruct);
                    if (in_array('encode_php_objs', $options)) {
                        // let's save original class name into xmlrpc value: it might be useful later on...
                        $xmlrpcVal->_php_class = get_class($phpVal);
                    }
                }
                break;
            case 'NULL':
                if (in_array('extension_api', $options)) {
                    $xmlrpcVal = new Value('', Value::$xmlrpcString);
                } elseif (in_array('null_extension', $options)) {
                    $xmlrpcVal = new Value('', Value::$xmlrpcNull);
                } else {
                    $xmlrpcVal = new Value();
                }
                break;
            case 'resource':
                if (in_array('extension_api', $options)) {
                    $xmlrpcVal = new Value((int)$phpVal, Value::$xmlrpcInt);
                } else {
                    $xmlrpcVal = new Value();
                }
                break;
            // catch "user function", "unknown type"
            default:
                // it has to return an empty object in case, not a boolean. (giancarlo pinerolo <ping@alt.it>)
                $xmlrpcVal = new Value();
                break;
        }

        return $xmlrpcVal;
    }

    /**
     * Convert the xml representation of a method response, method request or single
     * xmlrpc value into the appropriate object (a.k.a. deserialize).
     *
     * @param string $xmlVal
     * @param array $options
     * @return Value|Request|Response|false false on error, or an instance of either Value, Request or Response
     *
     * @todo is this a good name/class for this method? It does something quite different from 'decode' after all
     *       (returning objects vs returns plain php values)... In fact, it belongs rather to a Parser class
     * Feature creep -- we should allow an option to return php native types instead of PhpXmlRpc objects instances
     */
    public function decodeXml($xmlVal, $options = array())
    {
        // 'guestimate' encoding
        $valEncoding = XMLParser::guessEncoding('', $xmlVal);
        if ($valEncoding != '') {

            // Since parsing will fail if
            // - charset is not specified in the xml prologue,
            // - the encoding is not UTF8 and
            // - there are non-ascii chars in the text,
            // we try to work round that...
            // The following code might be better for mb_string enabled installs, but makes the lib about 200% slower...
            //if (!is_valid_charset($valEncoding, array('UTF-8'))
            if (!in_array($valEncoding, array('UTF-8', 'US-ASCII')) && !XMLParser::hasEncoding($xmlVal)) {
                /// @todo replace with function_exists
                if (extension_loaded('mbstring')) {
                    $xmlVal = mb_convert_encoding($xmlVal, 'UTF-8', $valEncoding);
                } else {
                    if ($valEncoding == 'ISO-8859-1') {
                        $xmlVal = utf8_encode($xmlVal);
                    } else {
                        $this->getLogger()->errorLog('XML-RPC: ' . __METHOD__ . ': invalid charset encoding of xml text: ' . $valEncoding);
                    }
                }
            }
        }

        // What if internal encoding is not in one of the 3 allowed? We use the broadest one, ie. utf8!
        if (!in_array(PhpXmlRpc::$xmlrpc_internalencoding, array('UTF-8', 'ISO-8859-1', 'US-ASCII'))) {
            /// @todo emit a warning
            $parserOptions = array(XML_OPTION_TARGET_ENCODING => 'UTF-8');
        } else {
            $parserOptions = array(XML_OPTION_TARGET_ENCODING => PhpXmlRpc::$xmlrpc_internalencoding);
        }

        $xmlRpcParser = $this->getParser();
        $xmlRpcParser->parse(
            $xmlVal,
            XMLParser::RETURN_XMLRPCVALS,
            XMLParser::ACCEPT_REQUEST | XMLParser::ACCEPT_RESPONSE | XMLParser::ACCEPT_VALUE | XMLParser::ACCEPT_FAULT,
            $parserOptions
        );

        if ($xmlRpcParser->_xh['isf'] > 1) {
            // test that $xmlrpc->_xh['value'] is an obj, too???

            $this->getLogger()->errorLog($xmlRpcParser->_xh['isf_reason']);

            return false;
        }

        switch ($xmlRpcParser->_xh['rt']) {
            case 'methodresponse':
                $v = $xmlRpcParser->_xh['value'];
                if ($xmlRpcParser->_xh['isf'] == 1) {
                    /** @var Value $vc */
                    $vc = $v['faultCode'];
                    /** @var Value $vs */
                    $vs = $v['faultString'];
                    $r = new Response(0, $vc->scalarval(), $vs->scalarval());
                } else {
                    $r = new Response($v);
                }
                return $r;

            case 'methodcall':
                $req = new Request($xmlRpcParser->_xh['method']);
                for ($i = 0; $i < count($xmlRpcParser->_xh['params']); $i++) {
                    $req->addParam($xmlRpcParser->_xh['params'][$i]);
                }
                return $req;

            case 'value':
                return $xmlRpcParser->_xh['value'];

            case 'fault':
                // EPI api emulation
                $v = $xmlRpcParser->_xh['value'];
                // use a known error code
                /** @var Value $vc */
                $vc = isset($v['faultCode']) ? $v['faultCode']->scalarval() : PhpXmlRpc::$xmlrpcerr['invalid_return'];
                /** @var Value $vs */
                $vs = isset($v['faultString']) ? $v['faultString']->scalarval() : '';
                if (!is_int($vc) || $vc == 0) {
                    $vc = PhpXmlRpc::$xmlrpcerr['invalid_return'];
                }
                return new Response(0, $vc, $vs);

            default:
                return false;
        }
    }
}
