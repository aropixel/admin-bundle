<?php

namespace Aropixel\AdminBundle\Domain\Seo;

class Seo
{
    /**
     * default charset to use.
     */
    public static string $charset = 'UTF-8';

    /**
     * banned words in english feel free to change them.
     */
    public static array $bannedWords = [];

    /**
     * min len for a word in the keywords.
     */
    public static int $minWordLength = 4;

    /**
     * SEO for text length
     * returns a text with text.
     *
     * @param int $length of the description
     */
    public static function text(?string $text, int $length = 160): string
    {
        return self::limitChars(self::clean($text), $length, '', true);
    }

    /**
     * gets the keyword from the text in the construct.
     *
     * @param int $max_keys number of keywords
     */
    public static function keywords(?string $text, int $max_keys = 15, bool $clean = true): string
    {
        if ($clean) {
            $text = self::clean(mb_strtolower((string) $text));
            $text = str_replace(['–', '(', ')', '+', ':', '.', '?', '!', '_', '*', '-', '"', "'"], '', $text); // replace not valid character
            $text = str_replace([' ', '.', ';'], ',', $text); // replace for comas
        }

        $wordcount = array_count_values(explode(',', (string) $text));

        // array to keep word->number of repetitions
        // $wordcount = array_count_values(str_word_count(self::clean($text),1));

        if ($clean) {
            // remove small words
            foreach ($wordcount as $key => $value) {
                if ((mb_strlen($key) <= self::$minWordLength) || \in_array($key, self::$bannedWords)) {
                    unset($wordcount[$key]);
                }
            }

            // sort keywords from most repetitions to less
            uasort($wordcount, ['self', 'cmp']);

            // keep only X keywords
            $wordcount = \array_slice($wordcount, 0, $max_keys);
        }

        // return keywords on a string
        return implode(', ', array_keys($wordcount));
    }

    /**
     * cleans an string from HTML spaces etc...
     *
     * @param string $text
     */
    private static function clean($text): string
    {
        $text = html_entity_decode($text, \ENT_QUOTES, self::$charset);
        $text = strip_tags($text); // erases any html markup
        $text = preg_replace('/\s\s+/', ' ', $text); // erase possible duplicated white spaces
        $text = str_replace(['\r\n', '\n', '+'], ',', $text); // replace possible returns

        return trim($text);
    }

    /**
     * sort for uasort descendent numbers , compares values.
     *
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    private static function cmp($a, $b): bool
    {
        return $b <=> $a;
    }

    /**
     * Limits a phrase to a given number of characters.
     * ported from kohana text class, so this class can remain as independent as possible
     *     $text = Text::limitChars($text);.
     *
     * @param string $str            phrase to limit characters of
     * @param int    $limit          number of characters to limit to
     * @param string $end_char       end character or entity
     * @param bool   $preserve_words enable or disable the preservation of words while limiting
     */
    private static function limitChars(?string $str, int $limit = 100, ?string $end_char = null, bool $preserve_words = false): string
    {
        $end_char ??= '…';

        $limit = (int) $limit;

        if ('' === trim((string) $str) || mb_strlen((string) $str) <= $limit) {
            return $str;
        }

        if ($limit <= 0) {
            return $end_char;
        }

        if (false === $preserve_words) {
            return rtrim(mb_substr((string) $str, 0, $limit)) . $end_char;
        }

        // Don't preserve words. The limit is considered the top limit.
        // No strings with a length longer than $limit should be returned.
        if (!preg_match('/^.{0,' . $limit . '}\s/us', (string) $str, $matches)) {
            return $end_char;
        }

        return rtrim($matches[0]) . ((mb_strlen($matches[0]) === mb_strlen((string) $str)) ? '' : $end_char);
    }
}
