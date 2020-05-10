<?php

namespace ConsoleArgs;

/**
 * Интерфейс всех параметров команд
 */
interface Parameter
{
    /**
     * Установка описания
     */
    public function setDescription (string $description): Parameter;

    /**
     * Установка локализации
     */
    public function setLocale (Locale $locale): Parameter;

    /**
     * Добавление алиаса
     */
    public function addAliase (string $name): Parameter;

    /**
     * Парсер значений
     */
    public function parse (array &$args);
}
