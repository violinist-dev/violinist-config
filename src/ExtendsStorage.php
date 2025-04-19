<?php

namespace Violinist\Config;

class ExtendsStorage
{
    private $items = [];

    public function addExtendItems(array $items_groups)
    {
        foreach ($items_groups as $item_group) {
            foreach ($item_group as $item) {
                $this->addExtendItem($item);
            }
        }
    }

    public function getExtendItems()
    {
        return $this->items;
    }

    public function addExtendItem(ExtendsChainItem $item)
    {
        $this->items[$item->getName()][$item->getKey()] = $item;
    }
}
