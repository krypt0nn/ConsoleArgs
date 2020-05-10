<?php

namespace ConsoleArgs;

/**
 * Объект команды по умолчанию
 * Выполняется если менеджеру была передана некорректная команда
 */
class DefaultCommand extends Command
{
    /**
     * Конструктор
     * 
     * [@param callable $callable = null] - анонимная функция для выполнения
     */
    public function __construct (callable $callable = null)
    {
        if ($callable !== null)
            $this->callable = $callable;
    }
}
