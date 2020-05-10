<?php

namespace ConsoleArgs;

/**
 * Объект команды вызова помощи (help)
 * Автоматически генерирует сообщение на основе информации о менеджере команд
 */
class HelpCommand extends Command
{
    /**
     * Конструктор
     * 
     * @param Manager $manager - менеджер команд, для которого будет строиться help
     */
    public function __construct (Manager $manager)
    {
        $this->name   = 'help';
        $this->locale = new Locale;

        $this->callable = function () use ($manager)
        {
            $commandMaxLength = 0;

            foreach ($manager->commands as $command)
                $commandMaxLength = max (strlen ($command->name), $commandMaxLength);

            echo PHP_EOL;

            foreach ($manager->commands as $command)
            {
                if ($command instanceof self)
                    continue;
                
                echo str_repeat (' ', $commandMaxLength - strlen ($command->name) + 1) . $command->name;

                if (sizeof ($command->aliases) > 0)
                    echo ' ('. implode (', ', $command->aliases) .')';

                if ($command->description)
                    echo ' — '. $command->description . PHP_EOL;

                if (sizeof ($command->params) > 0)
                {
                    $required = [];
                    $not_required = [];

                    foreach ($command->params as $param)
                        if ($param->required ?? false)
                            $required[] = $param;

                        else $not_required[] = $param;

                    if ($command->description)
                        echo str_repeat (' ', $commandMaxLength);

                    if (sizeof ($required) > 0)
                    {
                        echo '  Required:'. PHP_EOL;

                        $messages = [];
                        $messageMaxLength = 0;

                        foreach ($required as $id => $param)
                        {
                            $value = $param->defaultValue ?? '';
                            $value = $value ? (isset ($param->separator) ? ($param->separator != '' ? $param->separator .'"'. $value .'"' : $value) : ' "'. $value .'"') : '';

                            $messages[$id] = str_repeat (' ', $commandMaxLength + 5) . current ($param->names) . $value;

                            if (sizeof ($aliases = array_slice ($param->names, 1)) > 0)
                                $messages[$id] .= ' ('. implode (', ', $aliases) .')';

                            $messageMaxLength = max (strlen ($messages[$id]), $messageMaxLength);
                        }

                        foreach ($messages as $id => $message)
                        {
                            echo $message;
                            
                            if ($required[$id]->description)
                                echo str_repeat (' ', $messageMaxLength - strlen ($message)) .'   — '. $required[$id]->description;

                            echo PHP_EOL;
                        }
                    }

                    if (sizeof ($not_required) > 0)
                    {
                        if (sizeof ($required) > 0)
                            echo PHP_EOL . str_repeat (' ', $commandMaxLength + 3);

                        else echo '  ';
                        
                        echo 'Not required:'. PHP_EOL;

                        $messages = [];
                        $messageMaxLength = 0;

                        foreach ($not_required as $id => $param)
                        {
                            $value = $param->defaultValue ?? '';
                            $value = $value ? (isset ($param->separator) ? ($param->separator != '' ? $param->separator .'"'. $value .'"' : $value) : ' "'. $value .'"') : '';

                            $messages[$id] = str_repeat (' ', $commandMaxLength + 5) . current ($param->names) . $value;

                            if ($param instanceof Flag)
                                $messages[$id] .= ' [flag]';

                            if (sizeof ($aliases = array_slice ($param->names, 1)) > 0)
                                $messages[$id] .= ' ('. implode (', ', $aliases) .')';

                            $messageMaxLength = max (strlen ($messages[$id]), $messageMaxLength);
                        }

                        foreach ($messages as $id => $message)
                        {
                            echo $message;
                            
                            if ($not_required[$id]->description)
                                echo str_repeat (' ', $messageMaxLength - strlen ($message)) .'   — '. $not_required[$id]->description;

                            echo PHP_EOL;
                        }
                    }
                }

                echo PHP_EOL;
            }
        };
    }
}
