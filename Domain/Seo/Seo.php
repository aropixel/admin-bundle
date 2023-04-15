<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 27/01/2017 à 11:56
 */

namespace Aropixel\AdminBundle\Domain\Seo;


class Seo
{

    /**
     * default charset to use
     * @var string
     */
    public static string $charset = 'UTF-8';

    /**
     * banned words in english feel free to change them
     * @var array
     */
    public static array $bannedWords = array();

    /**
     * min len for a word in the keywords
     * @var integer
     */
    public static int $minWordLength = 4;

    /**
     * SEO for text length
     * returns a text with text
     * @param  integer $length of the description
     * @return string
     */
    static public function text(?string $text, int $length = 160) : string
    {
        return self::limitChars(self::clean($text), $length,'',TRUE);
    }

    /**
     * gets the keyword from the text in the construct
     *
     * @param  integer $max_keys number of keywords
     * @return string
     */
    static public function keywords(?string $text, int $max_keys = 15, bool $clean=true) : string
    {
        if ($clean) {
            $text = self::clean(mb_strtolower($text));
            $text = str_replace(array('–', '(', ')', '+', ':', '.', '?', '!', '_', '*', '-', '"', "'"), '', $text);//replace not valid character
            $text = str_replace(array(' ', '.', ';'), ',', $text);//replace for comas
        }

        $wordcount = array_count_values(explode(',', $text));

        //array to keep word->number of repetitions
        //$wordcount = array_count_values(str_word_count(self::clean($text),1));

        if ($clean) {

            //remove small words
            foreach ($wordcount as $key => $value) {
                if ((strlen($key) <= self::$minWordLength) OR in_array($key, self::$bannedWords))
                    unset($wordcount[$key]);
            }

            //sort keywords from most repetitions to less
            uasort($wordcount, array('self', 'cmp'));

            //keep only X keywords
            $wordcount = array_slice($wordcount, 0, $max_keys);

        }
        //return keywords on a string
        return implode(', ', array_keys($wordcount));
    }

    /**
     * cleans an string from HTML spaces etc...
     * @param  string $text
     * @return string
     */
    private static function clean($text) : string
    {
        $text = html_entity_decode($text,ENT_QUOTES,self::$charset);
        $text = strip_tags($text);//erases any html markup
        $text = preg_replace('/\s\s+/', ' ', $text);//erase possible duplicated white spaces
        $text = str_replace (array('\r\n', '\n', '+'), ',', $text);//replace possible returns
        return trim($text);
    }

    /**
     * sort for uasort descendent numbers , compares values
     * @param  integer $a
     * @param  integer $b
     * @return integer
     */
    private static function cmp($a, $b) : bool
    {
        if ($a == $b) return 0;

        return ($a < $b) ? 1 : -1;
    }

    /**
     * Limits a phrase to a given number of characters.
     * ported from kohana text class, so this class can remain as independent as possible
     *     $text = Text::limitChars($text);
     *
     * @param   string  $str            phrase to limit characters of
     * @param   integer $limit          number of characters to limit to
     * @param   string  $end_char       end character or entity
     * @param   boolean $preserve_words enable or disable the preservation of words while limiting
     * @return  string
     */
    private static function limitChars(?string $str, int $limit = 100, ?string $end_char = NULL, bool $preserve_words = FALSE) : string
    {
        $end_char = ($end_char === NULL) ? '…' : $end_char;

        $limit = (int) $limit;

        if (trim($str) === '' OR mb_strlen($str) <= $limit)
            return $str;

        if ($limit <= 0)
            return $end_char;

        if ($preserve_words === FALSE)
            return rtrim(mb_substr($str, 0, $limit)).$end_char;

        // Don't preserve words. The limit is considered the top limit.
        // No strings with a length longer than $limit should be returned.
        if ( ! preg_match('/^.{0,'.$limit.'}\s/us', $str, $matches))
            return $end_char;

        return rtrim($matches[0]).((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
    }
}
