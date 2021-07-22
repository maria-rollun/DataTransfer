<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

interface SetData
{
    /**
     * @param array|\stdClass|\ArrayAccess $data
     */
	public function setData($data): void;
}
