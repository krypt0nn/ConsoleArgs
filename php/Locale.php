<?php

namespace ConsoleArgs;

/**
 * Объект локализаций
 * Вы можете создать объект, указать в нём свои данные локализации и использовать его в командах, менеджере и т.п.
 * 
 * Параметры поддерживают возможность установки коллбэков. Аргументы коллбэков смотреть в описаниях к каждому параметру
 */
final class Locale
{
    /**
     * Неверный тип ->callable команды (к примеру, криворукий разработчик самостоятельно изменил его на, условно, 123)
     * 
     * Для callable
     * @param Command - передаёт объект команды
     */
    public $execution_error = '$callable must have type callable';

    /**
     * Неверный тип команды для менеджера
     * 
     * Для callable
     * @param Manager - менеджер команд
     * @param mixed   - переданная команда
     */
    public $command_type_exception = '$command must be ConsoleArgs\Command object or instance of it';

    /**
     * Не передана команда для выполнения
     */
    public $command_undefined_error = 'You should write any available command';

    /**
     * Передана несуществующая команда
     * 
     * Для callable
     * @param Manager - менеджер команд
     * @param string  - имя команды
     */
    public $command_not_exists_error = 'You should write only existing commands';

    /**
     * Исключение возникает если передан параметр, но после него не идёт значение
     * 
     * Для callable
     * @param Parameter - параметр без значения
     */
    public $unselected_value_exception = 'You should write param value';

    /**
     * Неверный тип параметра команды
     * 
     * Для callable
     * @param Command - передаёт объект команды
     * @param mixed   - передаётся параметр, который пытались установить команде
     */
    public $param_type_exception = '$param must be instance of ConsoleArgs\\Parameter interface';

    /**
     * Не передан обязательный параметр команды
     * 
     * Для string
     * %param_name% заменяется на название параметра
     * 
     * Для callable
     * @param Parameter - параметр для передачи
     */
    public $undefined_param_exception = 'You must define param %param_name%';

    /**
     * Попытка добавить уже существующий алиас к команде
     * 
     * Для callable
     * @param Command|Parameter - передаёт объект команды или параметра
     * @param string            - алиас, который пытались добавить
     */
    public $aliase_exists_exception = 'This aliase already exists';
}
