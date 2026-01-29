<?php

namespace App\Exception;

final class InsufficientStockException extends \RuntimeException
{
	/** @var string[] */
	private array $lines;

	/**
	 * @param string[] $lines
	 */
	public function __construct(array $lines)
	{
		$this->lines = $lines;
		parent::__construct('Brak na stanie');
	}

	/** @return string[] */
	public function getLines(): array
	{
		return $this->lines;
	}
}
