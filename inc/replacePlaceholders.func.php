<?php
/**
 * @file    replacePlaceholders.func.php
 *
 * @brief   Implementation of the replacePlaceholders() function.
 *
 * This file is part of PhpZabbixApi.
 *
 * PhpZabbixApi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpZabbixApi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpZabbixApi.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   GNU General Public License
 * @author      confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 */


/**
 * @brief   Replace placeholders in a string.
 *
 * Placeholders in the string are surrounded by '<' and '>' (e.g. '<FOOBAR>').
 *
 * @param   $string         Any string.
 * @param   $placeholders   Array with placeholders (key-value pair).
 *
 * @retval  string          Replaced string.
 */

function replacePlaceholders($string, $placeholders)
{
    foreach($placeholders as $placeholder => $value)
        $string = str_replace('<'.strtoupper($placeholder).'>', $value, $string);

    return $string;
}

?>
