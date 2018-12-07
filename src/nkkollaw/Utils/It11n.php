<?php
namespace nkkollaw\Utils;

class It11n {
    public static function isFirstName($first_name) {
        $data_file = realpath(__DIR__ . '/../../../') . '/data/nkkollaw/Utils/Italianization/nomi_italiani.txt';
        if (!is_file($data_file)) {
            throw new \Exception('data file not found while checking first name');
        }  

        $first_name = trim($first_name);
        $first_name = strtolower($first_name);
        $first_name = \nkkollaw\Utils\Strings::toAscii($first_name);

        $fp = fopen($data_file, 'r');
        while ($line = stream_get_line($fp, 1024 * 1024, "\n")) {
            if ($first_name == strtolower($line)) {
                fclose($fp);
                
                return true;
            }
        }
        fclose($fp);

        return false;
    }

    public static function explodeFullName($full_name) {
        $full_name = trim($full_name);
        $full_name = str_replace('  ', ' ', $full_name);
    
        $tokens = explode(' ', $full_name);
        if (count($tokens) > 4) {
            var_dump($tokens);
            throw new \Exception('full name contains more than 4 tokens, which is not supported for this locale');
        } 

        $first_name = '';
        $last_name = '';

        switch (count($tokens)) {
            case 1:
                if (self::isFirstName($tokens[0])) {
                    $first_name = $tokens[0];
                    $last_name = '';
                } else {
                    $first_name = '';
                    $last_name = $tokens[0];                                                   
                }
                break;
            case 2:
                // could be last name before first name, check both cases
                if (self::isFirstName($tokens[0])) {
                    $first_name = $tokens[0];
                    $last_name = $tokens[1];
                } elseif (self::isFirstName($tokens[1])) {
                    $first_name = $tokens[1];
                    $last_name = $tokens[0];
                }
                break;
            case 3:
                // could be compouned name, compouned last name, or other stuff
                // test all excluding weirder first
                $tests = [
                    // "Giovanni Giorgio Moroder"
                    [
                        'first_name' => $tokens[0] . ' ' . $tokens[1],
                        'last_name' => $tokens[2]
                    ],
                    // Moroder Giovanni Giorgio
                    [
                        'first_name' => $tokens[1] . ' ' . $tokens[2],
                        'last_name' => $tokens[0]
                    ],
                    // Robert De Niro
                    [
                        'first_name' => $tokens[0],
                        'last_name' => $tokens[1] . $tokens[2]
                    ],
                    // De Niro Robert
                    [
                        'first_name' => $tokens[2],
                        'last_name' => $tokens[0] . $tokens[1]
                    ]                                                                  
                ];
                foreach ($tests as $test) {
                    if (self::isFirstName($test['first_name'])) {
                        $first_name = $test['first_name'];
                        $last_name = $test['last_name'];                                                     
                        break;
                    }
                }
                break;
            case 4:
                // could be compouned name + compouned last name
                // test all excluding weirder first
                $tests = [
                    // ERMENEGILDA GRAZIA MARIA
                    [
                        'first_name' => $tokens[0] . ' ' . $tokens[1] . ' ' . $tokens[2],
                        'last_name' => $tokens[3],
                    ],                                                        
                    // "MARIA TERESA DE SCISCIO"
                    [
                        'first_name' => $tokens[0] . ' ' . $tokens[1],
                        'last_name' => $tokens[2] . ' ' . $tokens[3]
                    ],
                    // "DE SCISCIO MARIA TERESA"
                    [
                        'first_name' => $tokens[2] . ' ' . $tokens[3],
                        'last_name' => $tokens[0] . ' ' . $tokens[1],
                    ]
                ];
                foreach ($tests as $test) {
                    if (self::isFirstName($test['first_name'])) {
                        $first_name = $test['first_name'];
                        $last_name = $test['last_name'];                                                     
                        break;
                    }
                }
                break;
            default:
                throw new \Exception('full name contains more than 4 tokens, which is not supported for this locale. In addition, this error should have gotten caught sooner');
        }
    
        return [
            $first_name,
            $last_name
        ];
    }    
}