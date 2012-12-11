<?php
/**
 * Normalizer of external character.
 *
 * PHP 5.2
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/** GaijiNormalizerException */
require dirname(__FILE__).DIRECTORY_SEPARATOR.'GaijiNormalizerException.php';

/**
 * Normalize the external characters.
 */
class GaijiNormalizer {

    /** Character Encoding */
    const ENCODING = 'SJIS-win';

    /* Regular expression of ISO-2022-JP-MS */
    const ISO_2022_JP_MS = '/^e[ed][4-9a-f][0-9a-f]$/i';

    /**
     * Conversion rules
     *
     * @var array
     */
    private $conversionRules = array();

    /**
     * Convert the character encoding of shift JIS.
     * 
     * Normalize the character code.
     * For extended character code "Shift JIS".
     * 
     * @param String $from The source text.
     * @return String Normalized text.
     * @throws GaijiNormalizerException
     */
    public function convert($source) {
        $mbRegexEncoding = mb_regex_encoding();
        mb_regex_encoding(self::ENCODING);

        try {
            $replaced = $source;
            foreach ($this->conversionRules as $conversionRule) {
                extract($conversionRule, EXTR_OVERWRITE, null);
                $pattern     = $this->toMbCharacter($from);
                $replacement = $this->toMbCharacter($to);
                $replaced    = mb_ereg_replace($pattern, $replacement, $replaced);
                if ($replaced === false) {
                    $message = "Failed to replaced for normalization: {$from}->{$to}";
                    throw new GaijiNormalizerException($message);
                }
            }
            $normalizedText = $replaced;
        } catch (Exception $e) {
            mb_regex_encoding($mbRegexEncoding);
            throw $e;
        }
        mb_regex_encoding($mbRegexEncoding);

        return $normalizedText;
    }

    /**
     * Add a conversion rule.
     *
     * @param String $from The source character.
     * @param String $to Character code of converted.
     * @return void
     */
    public function addConversionRule($from, $to) {
        $conversionRule = array(
                'from' => $from,
                'to'   => $to,
            );
        $this->conversionRules[] = $conversionRule;
    }

    /**
     * Return a multibyte string character code.
     *
     * @param String $code Code of four-digit hexadecimal characters
     * @return String Character of binary data
     * @throws GaijiNormalizerException
     */
    private function toMbCharacter($code) {
        if (strlen($code) !== 4) {
            $message = "Not a character code of four hexadecimal digits: {$code}";
            throw new GaijiNormalizerException($message);
        }

        $mbCharacter = pack('H*', $code);
        if (!$this->isIso2022JpMs($code) && !$this->isValidSjis($mbCharacter)) {
            $message = "Invalid character of Shift_JIS encoding: {$code}";
            throw new GaijiNormalizerException($message);
        }

        return $mbCharacter;
    }

    /**
     * Check whether the character code of valid Shift_JIS
     *
     * @param String $characters The multibyte character string.
     * @return boolean Returns TRUE if the character code of valid Shift_JIS.
     */
    private function isValidSjis($characters) {
        $isValidSjis = mb_check_encoding($characters, self::ENCODING);

        return $isValidSjis;
    }

    /**
     * Check whether the character code of ISO-2022-JP-MS
     *
     * ED40 - ED4F
     * ED50 - ED5F
     * ED60 - ED6F
     * ED70 - ED7F
     * ED80 - ED8F
     * ED90 - ED9F
     * EDA0 - EDAF
     * EDB0 - EDBF
     * EDC0 - EDCF
     * EDD0 - EDDF
     * EDE0 - EDEF
     * EDF0 - EDFF
     * EE40 - EE4F
     * EE50 - EE5F
     * EE60 - EE6F
     * EE70 - EE7F
     * EE80 - EE8F
     * EE90 - EE9F
     * EEA0 - EEAF
     * EEB0 - EEBF
     * EEC0 - EECF
     * EED0 - EEDF
     * EEE0 - EEEF
     * EEF0 - EEFF
     *
     * @param String $code Code of four-digit hexadecimal characters
     * @return boolean Returns TRUE if the character code of ISO-2022-JP-MS.
     */
    private function isIso2022JpMs($code) {
        $isIso2022JpMs = (bool)preg_match(self::ISO_2022_JP_MS, $code);

        return $isIso2022JpMs;
    }
}
