<?php

namespace ConsoleArgs;

/**
 * Менеджер команд
 * Предоставляет возможность работы с командами через аргументы консоли
 */
class Manager
{
    public array $commands = [];
    public ?DefaultCommand $defaultCommand = null;
    
    protected Locale $locale;

    /**
     * Конструктор
     * 
     * [@param array $commands = []] - список команд
     * [@param DefaultCommand $defaultCommand = null] - объект дефолтной команды
     */
    public function __construct (array $commands = [], DefaultCommand $defaultCommand = null)
    {
        $this->locale = new Locale;
        $this->defaultCommand = $defaultCommand;

        foreach ($commands as $command)
            if ($command instanceof Command)
                $this->commands[$command->name] = $command;

            else throw new \Exception (is_callable ($this->locale->command_type_exception) ?
                ($this->locale->command_type_exception) ($this, $command) : $this->locale->command_type_exception);
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
     * Добавление новой команды менеджеру
     * 
     * @param Command $command - команда для добавления
     * 
     * @return Manager - возвращает сам себя
     */
    public function addCommand (Command $command): Manager
    {
        $this->commands[$command->name] = $command;

        return $this;
    }

    /**
     * Установка команды по умолчанию
     * 
     * @param DefaultCommand $defaultCommand - объект команды по умолчанию
     * 
     * @return Manager - возвращает сам себя
     */
    public function setDefault (DefaultCommand $defaultCommand): Manager
    {
        $this->defaultCommand = $defaultCommand;

        return $this;
    }

    /**
     * Итерация выполнения по аргументам
     * 
     * @throws \Exception - выбрасывает исключение если указан неверный тип $args
     * 
     * @param string|array $args - список аргументов консоли
     */
    public function execute ($args)
    {
        if (is_string ($args))
            return $this->execute (self::parse ($args));

        elseif (is_array ($args))
        {
            $args = array_values ($args);

            if (!isset ($args[0]))
            {
                if ($this->defaultCommand !== null)
                    return $this->defaultCommand->execute ($args);

                else throw new \Exception (is_callable ($this->locale->command_undefined_error) ?
                    ($this->locale->command_undefined_error) () : $this->locale->command_undefined_error);
            }

            $name  = $args[0];
            $dargs = array_slice ($args, 1);

            if (!isset ($this->commands[$name]))
            {
                foreach ($this->commands as $command)
                    if (in_array ($name, $command->aliases))
                        return $command->execute ($dargs);

                if ($this->defaultCommand !== null)
                    return $this->defaultCommand->execute ($args);

                throw new \Exception (is_callable ($this->locale->command_not_exists_error) ?
                    ($this->locale->command_not_exists_error) ($this, $name) : $this->locale->command_not_exists_error);
            }

            return $this->commands[$name]->execute ($dargs);
        }

        else throw new \Exception ('Incorrect $args type');
    }

    /**
     * Парсинг аргументов командной строки
     * 
     * @param string $args - строка для парсинга
     * 
     * @return array - возвращает аргументы из строки
     */
    public static function parse (string $args): array
    {
        $arguments = [];
        $argument  = '';

        $quotes   = ['"', '\''];
        $break    = null;
        $breakEnd = -1;

        for ($i = 0, $length = strlen ($args); $i < $length; ++$i)
        {
            if (
                in_array ($args[$i], $quotes) &&
                (($break !== null && $break == $args[$i]) || $break === null) && 
                (
                    ($break === null && ($i == 0 || $args[$i - 1] == ' ')) ||
                    ($break !== null && ($i + 1 == $length || $args[$i + 1] == ' '))
                )
            )
            {
                $shield = false;

                for ($j = $i - 1; $args[$j] == '\\'; --$j)
                    $shield = !$shield;

                if (!$shield)
                    $break = $break === null ? $args[$i] : null;

                if ($break === null)
                    $breakEnd = $i + 1;

                continue;
            }

            if ($args[$i] == ' ' && $break === null)
            {
                if ($argument != '' || $breakEnd == $i)
                {
                    $arguments[] = $argument;
                    $argument    = '';
                }

                continue;
            }

            $argument .= $args[$i];
        }

        if ($argument != '')
        {
            if ($break !== null)
            {
                $arguments[] = $break;

                $arguments = array_merge ($arguments, self::parse (substr ($argument, 1)));
            }

            else $arguments[] = $argument;
        }

        return array_map ('stripslashes', $arguments);
    }
}
