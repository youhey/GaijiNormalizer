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
        if (!mb_check_encoding($mbCharacter, self::ENCODING)) {
            $message = "Invalid character of Shift_JIS encoding: {$code}";
            throw new GaijiNormalizerException($message);
        }

        return $mbCharacter;
    }
}
