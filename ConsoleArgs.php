<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     ConsoleArgs
 * @copyright   2019 Podvirnyy Nikita (KRypt0n_)
 * @license     GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @license     Enfesto Studio Group license <https://vk.com/topic-113350174_36400959>
 * @author      Podvirnyy Nikita (KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    vk.com/technomindlp
 *        vk.com/hphp_convertation
 * 
 */

namespace ConsoleArgs;

/**
 * Объект локализаций
 * Вы можете создать объект, указать в нём свои данные локализации и использовать его в командах, менеджере и т.п.
 */
class Locale
{
    public $execution_error            = '$callable must be any closure';
    public $command_type_exception     = '$command must be ConsoleArgs\Command object or instance of him';
    public $command_undefined_error    = 'You should write any available command';
    public $unselected_value_exception = 'You should write param value';
    public $param_type_exception       = '$param must be ConsoleArgs\Param or ConsoleArgs\Flag object or instance of him';
    public $undefined_param_exception  = 'You must define this param';
}

/**
 * Объект флагов
 * Отвечает за создание флагов для команд
 */
class Flag
{
    public $name;

    /**
     * Конструктор
     * 
     * @param string $name - имя флага
     */
    public function __construct (string $name)
    {
        $this->name = $name;
    }

    /**
     * Парсер флагов
     * 
     * @param array &$args - массив аргументов для парсинга
     * 
     * Возвращает состояние флага
     */
    public function parse (array &$args): bool
    {
        $args = array_values ($args);

        if (($key = array_search ($this->name, $args)) !== false)
        {
            unset ($args[$key]);
            $args = array_values ($args);

            while ($this->parse ($args) !== false);
            
            return true;
        }

        return false;
    }
}

/**
 * Объект параметров
 * Отвечает за объявление параметров команд
 */
class Param
{
    public $name;
    public $defaultValue;
    public $required;
    protected $locale;

    /**
     * Конструктор
     * 
     * @param string $name - имя парамтера
     * [@param string $defaultValue = null] - дефолтное значение параметра
     */
    public function __construct (string $name, string $defaultValue = null, bool $required = false)
    {
        $this->name         = $name;
        $this->defaultValue = $defaultValue;
        $this->required     = $required;

        $this->locale = new Locale;
    }

    /**
     * Установка локализации
     * 
     * @param Locale $locale - объект локализации
     * 
     * @return Param - возвращает сам себя
     */
    public function setLocale (Locale $locale): Param
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Парсер параметров
     * 
     * @param array &$args - массив аргументов для парсинга
     * 
     * Возвращает найденый параметр или массив найдёных параметров, если их было указано несколько
     */
    public function parse (array &$args)
    {
        $args = array_values ($args);

        if (($key = array_search ($this->name, $args)) !== false)
        {
            if (!isset ($args[$key + 1]))
                throw new \Exception ($this->locale->unselected_value_exception);

            $param = [$args[$key + 1]];

            unset ($args[$key], $args[$key + 1]);
            $args = array_values ($args);

            while (true)
                try
                {
                    $param[] = $this->parse ($args);
                }

                catch (\Throwable $e)
                {
                    break;
                }
            
            return sizeof ($param) == 1 ?
                $param[0] : $param;
        }

        if ($this->required)
            throw new \Exception ($this->locale->undefined_param_exception);

        return $this->defaultValue;
    }
}

/**
 * Объект команд
 * Отвечает за выполнение команд и работу с параметрами
 */
class Command
{
    public $name;
    public $callable;
    public $params = [];

    protected $locale;

    /**
     * Конструктор
     * 
     * @param string $name - имя команды
     * [@param \Closure $callable = null] - анонимная функция для выполнения
     */
    public function __construct (string $name, \Closure $callable = null)
    {
        $this->name   = $name;
        $this->locale = new Locale;

        if ($callable !== null)
            $this->callable = $callable;
    }

    /**
     * Установка локализации
     * 
     * @param Locale $locale - объект локализации
     * 
     * @return Command - возвращает сам себя
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
     * @return Command - возвращает сам себя
     */
    public function addParams (array $params): Command
    {
        foreach ($params as $param)
            if ($param instanceof Param || $param instanceof Flag)
                $this->params[$param->name] = $param;

            else throw new \Exception ($this->locale->param_type_exception);

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
        if ($this->callable instanceof \Closure)
        {
            $params = $this->getParams ($args);

            return $this->callable->call ($this, array_values ($args), $params);
        }

        throw new \Exception ($this->locale->execution_error);
    }
}

/**
 * Менеджер команд
 * Предоставляет возможность работы с командами через аргументы консоли
 */
class Manager
{
    public $commands = [];
    protected $locale;

    /**
     * Конструктор
     * 
     * @param array $commands - список команд
     */
    public function __construct (array $commands)
    {
        $this->locale = new Locale;

        foreach ($commands as $command)
            if ($command instanceof Command)
                $this->commands[$command->name] = $command;

            else throw new \Exception ($this->locale->command_type_exception);
    }

    /**
     * Установка локализации
     * 
     * @param Locale $locale - объект локализации
     * 
     * @return Manager - возвращает сам себя
     */
    public function setLocale (Locale $locale): Manager
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Итерация выполнения по аргументам
     * 
     * @param array $args - список аргументов консоли
     */
    public function execute (array $args)
    {
        $args = array_values ($args);

        if (!isset ($args[0]))
            throw new \Exception ($this->locale->command_undefined_error);

        if (!isset ($this->commands[$args[0]]))
            return false;

        $name = $args[0];
        $args = array_slice ($args, 1);

        return $this->commands[$name]->execute ($args);
    }
}
