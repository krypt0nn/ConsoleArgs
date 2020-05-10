<?php

namespace ConsoleArgs;

/**
 * Объект флагов
 * Отвечает за создание флагов для команд
 */
class Flag implements Parameter
{
    public array $names;
    public ?string $description = null; // Описание параметра, которое будет прикреплено к HelpCommand

    protected Locale $locale;

    /**
     * Конструктор
     * 
     * @param string $name - имя флага
     */
    public function __construct (string $name)
    {
        $this->names  = [$name];
        $this->locale = new Locale;
    }

    /**
     * Установка описания
     * 
     * @param string $description - описание
     * 
     * @return Parameter
     */
    public function setDescription (string $description): Parameter
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Установка локализации
     * 
     * @param Locale $locale - объект локализации
     * 
     * @return Parameter
     */
    public function setLocale (Locale $locale): Parameter
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Добавление алиаса
     * 
     * @param string $name - алиас для добавления
     * 
     * @return Parameter
     */
    public function addAliase (string $name): Parameter
    {
        if (in_array ($name, $this->names))
            throw new \Exception (is_callable ($this->locale->aliase_exists_exception) ?
                ($this->locale->aliase_exists_exception) ($this, $name) : $this->locale->aliase_exists_exception);

        $this->names[] = $name;

        return $this;
    }

    /**
     * Парсер флагов
     * 
     * @param array &$args - массив аргументов для парсинга
     * 
     * Возвращает состояние флага
     */
    public function parse (array &$args)
    {
        $args = array_values ($args);

        foreach ($this->names as $name)
            if (($key = array_search ($name, $args)) !== false)
            {
                unset ($args[$key]);
                $args = array_values ($args);

                while ($this->parse ($args) !== false);
                
                return true;
            }

        return false;
    }
}
