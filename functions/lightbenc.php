<?php

/**
 * @author Kevin
 * @copyright 2014
 * @info Light version of BDecode and Bencode
 */

class lightbenc
{
    function bdecode($s, &$pos = 0)
    {
        if ($pos >= strlen($s))
        {
            return null;
        }
        switch ($s[$pos])
        {
            case "d":
                $pos++;
                $retval = array();
                while ($s[$pos] != "e")
                {
                    $key = self::bdecode($s, $pos);
                    $val = self::bdecode($s, $pos);
                    if ($key === null || $val === null)
                        break;
                    $retval[$key] = $val;
                }
                $retval["isDct"] = true;
                $pos++;
                return $retval;

            case "l":
                $pos++;
                $retval = array();
                while ($s[$pos] != "e")
                {
                    $val = self::bdecode($s, $pos);
                    if ($val === null)
                        break;
                    $retval[] = $val;
                }
                $pos++;
                return $retval;

            case "i":
                $pos++;
                $digits = strpos($s, "e", $pos) - $pos;
                $val = (int)substr($s, $pos, $digits);
                $pos += $digits + 1;
                return $val;

                //	case "0": case "1": case "2": case "3": case "4":
                //	case "5": case "6": case "7": case "8": case "9":
            default:
                $digits = strpos($s, ":", $pos) - $pos;
                if ($digits < 0 || $digits > 20)
                    return null;
                $len = (int)substr($s, $pos, $digits);
                $pos += $digits + 1;
                $str = substr($s, $pos, $len);
                $pos += $len;
                //echo "pos: $pos str: [$str] len: $len digits: $digits\n";
                return (string )$str;
        }
        return null;
    }

    function bencode(&$d)
    {
        if (is_array($d))
        {
            //Nodig, anders krijgen we een warning
            $isDict = 0;

            $ret = "l";
            if (isset($d["isDct"]) && $d["isDct"])
            {
                $isDict = 1;
                $ret = "d";
                // this is required by the specs, and BitTornado actualy chokes on unsorted dictionaries
                ksort($d, SORT_STRING);
            }
            foreach ($d as $key => $value)
            {
                if ($isDict)
                {
                    // skip the isDct element, only if it's set by us
                    if ($key == "isDct" and is_bool($value))
                        continue;
                    $ret .= strlen($key) . ":" . $key;
                }
                if (is_string($value))
                {
                    $ret .= strlen($value) . ":" . $value;
                }
                elseif (is_int($value))
                {
                    $ret .= "i${value}e";
                } else
                {
                    $ret .= self::bencode($value);
                }
            }
            return $ret . "e";
        }
        // fallback if we're given a single bencoded string or int
        elseif (is_string($d))
        {
            return strlen($d) . ":" . $d;
        }
        elseif (is_int($d))
        {
            return "i${d}e";
        } else
        {
            return null;
        }
    }
}

function lightBenc($data, $method)
{   
    //Class call
    $benc = new lightbenc;

    //Decode
    if ($method == "decode")
    {
        return $benc->bdecode($data);
    }
    //Encode
    elseif ($method == "encode")
    {
        return $benc->bencode($data);
    }
}

?>