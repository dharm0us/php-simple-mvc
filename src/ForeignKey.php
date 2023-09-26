<?php

namespace SimpleMVC;

class ForeignKey
{
    public function __construct(
        public string $columnName,
        public string $refTable,
        public string $refColumn
    ) {
    }
}
