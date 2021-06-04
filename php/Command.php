<?php

namespace ConsoleArgs;

/**
 * Объект команд
 * Отвечает за выполнение команд и работу с параметрами
 */
class Command
{
    public string $name; // Название команды
    public ?string $description = null; // Описание команды, которое будет прикреплено к HelpCommand

    public $callable;
    public array $params  = [];
    public array $aliases = [];

    protected Locale $locale;

    /**
     * Конструктор
     * 
     * @param string $name - имя команды
     * [@param callable $callable = null] - анонимная функция для выполнения
     */
    public function __construct (string $name, callable $callable = null)
    {
        $this->name   = $name;
        $this->locale = new Locale;

        if ($callable !== null)
            $this->callable = $callable;
    }

    /**
     * Установка описания команде
     * 
     * @param string $description - описание
     * 
     * @return Command
     */
    public function setDescription (string $description): Command
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Установка локализации
     * 
     * @param Locale $locale - объект локализации
     * 
     * @return Command
     */
    public function setLocale (Locale $locale): Command
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Установка параметров
     * 
     * @param array $params - список параметров для установки
     * 
     * @return Command
     */
    public function addParams (array $params): Command
    {
        foreach ($params as $param)
            if ($param instanceof Parameter)
                $this->params[current ($param->names)] = $param;

            else throw new \Exception (is_callable ($this->locale->param_type_exception) ?
                ($this->locale->param_type_exception) ($this, $param) : $this->locale->param_type_exception);

        return $this;
    }

    /**
     * Добавление алиаса
     * 
     * @param string $name - алиас для добавления
     * 
     * @return Command
     */
    public function addAlias (string $name): Command
    {
        if (in_array ($name, $this->aliases))
            throw new \Exception (is_callable ($this->locale->alias_exists_exception) ?
                ($this->locale->alias_exists_exception) ($this, $name) : $this->locale->alias_exists_exception);

        $this->aliases[] = $name;

        return $this;
    }

    /**
     * Парсинг параметров
     * 
     * @param array &$args - аргументы для парсинга
     * 
     * @return array - возвращает ассоциативный массив [параметр] => [значение]
     */
    public function getParams (array &$args): array
    {
        $params = array_combine (array_keys ($this->params), array_fill (0, sizeof ($this->params), null));

        foreach ($this->params as $name => $param)
            $params[$name] = $param->parse ($args);

        return $params;
    }

    /**
     * Выполнение команды
     * 
     * @param array &$args - аргументы команды
     */
    public function execute (array &$args)
    {
        if (is_callable ($this->callable))
        {
            $params = $this->getParams ($args);

            return $this->callable->call ($this, array_values ($args), $params);
        }

        throw new \Exception (is_callable ($this->locale->execution_error) ?
            ($this->locale->execution_error) ($this) : $this->locale->execution_error);
    }
}
