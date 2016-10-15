<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

class Text
{
    const WYSIWYG_BREAK_HTML = '<div style="page-break-after: always">';

    /**
     * Make snippet from text or html-text content.
     * @param string $text
     * @param int $length
     * @return string
     */
    public static function snippet($text, $length = 150)
    {
        // check if valid string is passed
        if (!Obj::isString($text)) {
            return null;
        }

        $breakerPos = mb_strpos($text, self::WYSIWYG_BREAK_HTML, null, 'UTF-8');
        // offset is founded, try to split preview from full text
        if ($breakerPos !== false) {
            $text = Str::sub($text, 0, $breakerPos);
        } else { // page breaker is not founded, lets get a fun ;D
            // find first paragraph ending
            $breakerPos = mb_strpos($text, '</p>', null, 'UTF-8');
            // no paragraph's ? lets try to get <br[\/|*]>
            if ($breakerPos === false) {
                $breakerPos = mb_strpos($text, '<br', null, 'UTF-8');
            } else {
                // add length('</p>')
                $breakerPos+= 4;
            }
            // cut text from position caret before </p> (+4 symbols to save item as valid)
            if ($breakerPos !== false) {
                $text = Str::sub($text, 0, $breakerPos);
            }
        }

        // if breaker position is still undefined - lets make 'text cut' for defined length and remove all html tags
        if ($breakerPos === false) {
            $text = strip_tags($text);
            $text = self::cut($text, 0, $length);
        }

        return $text;
    }

    /**
     * Cut text starting from $start pointer to $end pointer in UTF-8 mod
     * @param string $text
     * @param int $start
     * @param int $end
     * @return string
     */
    public static function cut($text, $start = 0, $end = 0)
    {
        return Str::sub($text, $start, $end);
    }

}