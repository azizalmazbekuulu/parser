<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Removes line breaks and repeated whitespace characters
     * @param string $string
     * @return string
     */
    public static function removeSpaces( string $string ): string
    {
        $string = str_replace( "\n", '', $string );
        return trim( preg_replace( '/[ \s]+/u', ' ', $string ) );
    }

    /**
     * Removes tabulation, carriage transfer. Removes duplicate line breaks and whitespace characters
     * @param string $string
     * @return string
     */
    public static function normalizeSpaceInString( string $string ): string
    {
        $string = trim( str_replace( ' ', ' ', $string ) );
        $string = preg_replace( '/( )+/', " ", $string );
        $string = preg_replace( [ '/\t+(( )+)?/', '/\r+(( )+)?/' ], '', $string );
        $string = preg_replace( '/\n(( )+)?/', "\n", $string );
        return preg_replace( '/\n+/', "\n", $string );
    }

    /**
     * Splits the text into paragraphs according to the specified number of sentences, if the source text does not contain html tags
     * @param string $string Text without html tags
     * @param int $size Number of sentences in one paragraph
     * @return string Formatted text
     */
    public static function paragraphing( string $string, int $size = 3 ): string
    {
        if ( $string === strip_tags( $string ) ) {
            $text = '';
            $paragraphs = array_chunk( preg_split( '/(?<=[.?!;])\s+(?=\p{Lu})/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ), $size );
            foreach ( $paragraphs as $paragraph ) {
                $string = implode( ' ', $paragraph );
                $text .= "<p>$string</p>";
            }
            return $text;
        }
        return $string;
    }

    /**
     * Checks whether the string is not empty
     * @param string|null $string
     * @return bool
     */
    public static function isNotEmpty( ?string $string ): bool
    {
        if ( empty( $string ) ) {
            return false;
        }
        return !empty( preg_replace( '/\s+/', '', self::removeSpaces( $string ) ) );
    }

    /**
     * Cuts out block tags and hyperlink tags with their contents, clearing the remaining tags from all attributes.
     * @param string $string
     * @param bool $flag
     * @param array $tags
     * @return null|string
     */
    public static function cutTags( string $string, bool $flag = true, array $tags = [] ): ?string
    {
        $mass = [
            'span',
            'p',
            'br',
            'ol',
            'ul',
            'li',
            'table',
            'thead',
            'tbody',
            'th',
            'tr',
            'td',
        ];

        $regexps = [
            '/<script[^>]*?>.*?<\/script>/i',
            '/<noscript[^>]*?>.*?<\/noscript>/i',
            '/<style[^>]*?>.*?<\/style>/i',
            '/<video[^>]*?>.*?<\/video>/i',
            '/<a[^>]*?>.*?<\/a>/i',
            '/<iframe[^>]*?>.*?<\/iframe>/i'
        ];
        foreach ( $regexps as $regexp ) {
            if ( preg_match( $regexp, $string ) ) {
                $string = (string)preg_replace( $regexp, '', $string );
            }
        }

        $string = (string)self::mb_trim( $string );
        if ( !$flag ) {
            $mass = [];
        }

        if ( !empty( $tags ) && is_array( $tags ) ) {
            foreach ( $tags as $tag ) {
                $regexp = '/<(\D+)\s?[^>]*?>/';
                if ( preg_match( $regexp, $tag, $matches ) ) {
                    $mass[] = $matches[ 1 ];
                }
                else {
                    $mass[] = $tag;
                }
            }
        }

        $tags_string = '';
        foreach ( $mass as $tag ) {
            $tags_string .= "<$tag>";
        }

        $string = strip_tags( $string, $tags_string );
        foreach ( $mass as $tag ) {

            $regexp = "/(<$tag)([^>]*)(>)/i";

            if ( preg_match( $regexp, $string ) ) {
                $string = (string)preg_replace( $regexp, '$1$3', $string );
            }
        }
        return $string;
    }

    /**
     * Cuts out all tag attributes
     * @param string $string
     * @return string
     */
    public static function cutTagsAttributes( string $string ): string
    {
        return preg_replace( '/(<[a-z]+)([^>]*)(>)/i', '$1$3', $string );
    }

    /**
     * Cuts out empty tags
     * @param string $string
     * @return string
     */
    public static function cutEmptyTags( string $string ): string
    {
        $string = preg_replace( '/<\w+>(\s+)?<\/\w+>/', '', $string );
        if ( preg_match( '/<\w+>(\s+)?<\/\w+>/', $string ) ) {
            $string = self::cutEmptyTags( $string );
        }
        return $string;
    }


    public static function mb_ucfirst( $string, $encoding = 'UTF-8' ): string
    {
        $strlen = mb_strlen( $string, $encoding );
        $firstChar = mb_substr( $string, 0, 1, $encoding );
        $then = mb_substr( $string, 1, $strlen - 1, $encoding );
        return mb_strtoupper( $firstChar, $encoding ) . $then;
    }

    public static function mb_ucwords( $string, $encoding = 'UTF-8' ): string
    {
        $upper_words = array();
        $words = explode( ' ', $string );

        foreach ( $words as $word ) {
            $upper_words[] = self::mb_ucfirst( $word, $encoding );
        }

        return implode( ' ', $upper_words );

    }

    /**
     * @param $string
     * @param string|string[] $trim_chars
     * @return string|string[]|null
     */
    public static function mb_trim( $string, array|string $trim_chars = "\s" )
    {
        return preg_replace( '/^[' . $trim_chars . ']*(?U)(.*)[' . $trim_chars . ']*$/u', '\\1', $string );
    }

    private static function UPC_calculate_check_digit( $upc_code )
    {
        $sum = 0;
        $mult = 3;
        for ( $i = ( \strlen( $upc_code ) - 2 ); $i >= 0; $i-- ) {
            $sum += $mult * $upc_code[ $i ];
            if ( $mult == 3 ) {
                $mult = 1;
            }
            else {
                $mult = 3;
            }
        }
        if ( $sum % 10 == 0 ) {
            $sum = ( $sum % 10 );
        }
        else {
            $sum = 10 - ( $sum % 10 );
        }
        return $sum;
    }

    private static function isISBN( $sCode )
    {
        $bResult = false;
        if ( \in_array( strlen( $sCode ), [ 10, 13 ], true ) && \in_array( substr( $sCode, 0, 3 ), [ 978, 979 ], true ) ) {
            $bResult = true;
        }
        return $bResult;
    }

    public static function calculateUPC( $upc_code )
    {
        $upc_code = preg_replace( '/[^0-9]/', '', $upc_code );
        switch ( strlen( $upc_code ) ) {
            case 8:
            case 14:
                $cd = self::UPC_calculate_check_digit( $upc_code );
                if ( $cd != $upc_code[ strlen( $upc_code ) - 1 ] ) {
                    return substr( $upc_code, 0, -1 ) . $cd;
                }

                return $upc_code;
            case 11:
            case 12:
            case 13:
                $cd = self::UPC_calculate_check_digit( $upc_code );
                if ( $cd != $upc_code[ strlen( $upc_code ) - 1 ] ) {
                    if ( !self::isISBN( $upc_code ) || ( self::isISBN( $upc_code ) && strlen( $upc_code ) === 12 ) ) {
                        $cd = self::UPC_calculate_check_digit( $upc_code . '1' );
                        return $upc_code . $cd;
                    }

                    return '';
                }

                return $upc_code;
        }
        return '';
    }

    /**
     * parser size inch or foot from string to inch float
     *
     * @param string $size inch/foot string ex. 1 2/3" or 2.5'
     * @return null|float float when successful parse else false
     */
    public static function parseInch( string $size ): ?float
    {
        $replacements = [
            '”' => '"',
            '’' => '\'',
            '¼' => '1/4',
            '½' => '1/2',
            '¾' => '3/4',
        ];

        $size = str_replace( array_keys( $replacements ), array_values( $replacements ), $size );
        $size = trim( $size );

        if ( preg_match( '/[\d]+\.?[\d]*?/', $size ) === 0 ) {
            return null;
        }

        $mul = $size[ strlen( $size ) - 1 ] === '"' ? 1 : 12;
        $size = trim( $size, '"\'' );
        $parts = explode( ' ', $size );
        $int = 0;
        $float = 0;

        if ( is_numeric( $parts[ 0 ] ) ) {
            $int = $parts[ 0 ];
            $float = $parts[ 1 ] ?? null ?: 0;
        }
        else {
            $float = $parts[ 0 ];
        }

        if ( !is_numeric( $float ) && str_contains( $float, '/' ) ) {
            $parts = explode( '/', $float );
            if ( is_numeric( $parts[ 0 ] ) && is_numeric( $parts[ 1 ] ) ) {
                $float = (float)$parts[ 0 ] / (float)$parts[ 1 ];
            }
        }

        return ( (float)$int + (float)$float ) * $mul;
    }

    public static function getMoney( string $price ): float
    {
        $price = str_replace( ',', '', $price );
        preg_match( '/\d+\.?(\d?)+/', $price, $matches );
        return (float)( $matches[ 0 ] ?? 0.0 );
    }

    public static function existsMoney( string $string ): string
    {
        $currency = [
            '\\\u00a3', '&pound;', '\$', '£'
        ];
        foreach ( $currency as $c ) {
            if ( preg_match( "/$c(\s+)?((\d+)?(\.?\d+))/", $string, $match ) ) {
                return $match[ 0 ];
            }
        }
        return '';
    }

    public static function getFloat( string $string, ?float $default = null ): ?float
    {
        if ( preg_match( '/\d+\.\d+|\.\d+|\d+/', str_replace( ',', '', $string ), $match_float ) ) {
            return self::normalizeFloat( (float)$match_float[ 0 ], $default );
        }
        return null;
    }

    public static function normalizeFloat( ?float $float, ?float $default = null ): ?float
    {
        $float = round( $float, 2 );
        return $float > 0.01 ? $float : $default;
    }

    public static function normalizeSrcLink( $link, $domain ): string
    {
        $cleared_link = ltrim( str_replace( [ '../', './', '\\' ], '', $link ), '/' );
        $parsed_domain = parse_url( $domain );

        preg_match( '~^(?:(?<protocol>(?:ht|f)tps?)://)?(?<domain_name>[\pL\d.-]+\.(?<zone>\pL{2,4}))~iu', $cleared_link, $matches );

        if ( empty( $matches[ 'domain_name' ] ) ) {
            return $parsed_domain[ 'scheme' ] . '://' . $parsed_domain[ 'host' ] . '/' . $cleared_link;
        }

        if ( empty( $matches[ 'protocol' ] ) ) {
            return $parsed_domain[ 'scheme' ] . '://' . $cleared_link;
        }

        return $cleared_link;
    }


}
