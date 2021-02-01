<?php
namespace App\Utils;

/**
 * Хелпер по работе с генерацией slug
 */
class SlugHelper
{
    /**
     * @var array Словарь транслитерации текста
     */
    public static array $slugTransliteratorDic = [
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'yo',  'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'i',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',    'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
    ];

    /**
     * @param string $text Исходный текст
     * @param int $limit Ограничение на количество слов
     * @return string slug на основе указанной текстовой строки
     */
    public static function generate(string $text, int $limit = 5): string
    {
        // приведем строку к нижнему регистру
        $text = mb_strtolower($text);

        // используем одно тире как разделитель слов
        $text = trim(preg_replace('/[-\s]+/', '-', $text), '-');

        // удалим все небуквенные символы
        $text = preg_replace('/[^-\p{L}0-9]+/u', '', $text);

        // транслитерация
        $text = strtr($text, static::$slugTransliteratorDic);

        // ограничение на количество букв в слове
        $words = array_filter(explode('-', $text), function ($word) {
            return mb_strlen($word) > 2;
        });

        // ограничим количество слов
        if ($limit) {
            $words = array_slice($words, 0, $limit);
        }

        return implode('-', $words);
    }
}
