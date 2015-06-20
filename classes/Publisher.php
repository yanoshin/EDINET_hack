<?php
/**
 * Created by IntelliJ IDEA.
 * User: yanoshin
 * Date: 6/20/15
 * Time: 13:40
 */


class Publisher {
    private $name;
    private $ticker;
    private $otc;
    private $stock_listing;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getOtc()
    {
        return $this->otc;
    }

    /**
     * @param mixed $otc
     */
    public function setOtc($otc)
    {
        $this->otc = $otc;
    }

    /**
     * @return mixed
     */
    public function getStockListing()
    {
        return $this->stock_listing;
    }

    /**
     * @param mixed $stock_listing
     */
    public function setStockListing($stock_listing)
    {
        $this->stock_listing = $stock_listing;
    }

    /**
     * @return mixed
     */
    public function getTicker()
    {
        return $this->ticker;
    }

    /**
     * @param mixed $ticker
     */
    public function setTicker($ticker)
    {
        $this->ticker = $ticker;
    }

}
