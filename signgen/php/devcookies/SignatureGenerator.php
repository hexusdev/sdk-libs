<?php

namespace devcookies;

use \InvalidArgumentException;

/**
 *
 * @author devcookies
 * @copyright 2013
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is  furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */
class SignatureGenerator
{
	const MAXIMUM_RECURSION_DEPTH = 2;
	const MAXIMUM_RECURSION_DEPTH_FOR_SUBFORM = 3;

	private $salt = "";
	private $ignoreInvalidValues = false;

	/**
	 * @param string $salt
	 * @param string $ignoreUnsupportedValues
	 * @throws InvalidArgumentException
	 */
	public function __construct($salt, $ignoreUnsupportedValues = false)
	{
		if (!is_string($salt) || !$salt)
			throw new InvalidArgumentException("\$salt required to be non empty string!");

		if (!is_bool($ignoreUnsupportedValues))
			throw new InvalidArgumentException("\$ignoreUnsupportedValues required to be boolean!");

		$this->salt = $salt;
		$this->ignoreInvalidValues = $ignoreUnsupportedValues;
	}

	/**
	 * @param array $params supported types for array values (of any level): boolean, string, array or null
	 * @throws InvalidArgumentException
	 * @return string
	 */
	public function assemble(array $params, $maxLevelDepth = self::MAXIMUM_RECURSION_DEPTH)
	{
		if (empty($params))
			throw new InvalidArgumentException("Empty params passed!");

		return sha1($this->parseArray($params, 1, $maxLevelDepth) . ";" . $this->salt);
	}

	private function parseArray(array $params, $level, $maxLevelDepth)
	{
		if ($level > $maxLevelDepth)
			return "";

		$paramsToSign = array();

		ksort($params);

		foreach ($params as $key => $value) {
			$valueToAdd = "";

			switch (true) {
				case is_bool($value):
					$valueToAdd = $value ? "1" : "0";
					break;

				case is_scalar($value) && !is_resource($value):
					$valueToAdd = (string) $value;
					break;

				case is_array($value):
					$valueToAdd = $this->parseArray($value, $level + 1, $maxLevelDepth);
					break;

				case is_null($value):
					break;

				default:
					if (!$this->ignoreInvalidValues)
						throw new InvalidArgumentException(
							"Type of value for key: \"{$key}\" is not supported."
							. " Supported types are: boolean, string, array and null"
						);
					continue 2;
			}

			if ($valueToAdd === "") continue;

			$paramsToSign[$key] = $key . ':' . $valueToAdd;
		}

		return implode(";", $paramsToSign);
	}
}