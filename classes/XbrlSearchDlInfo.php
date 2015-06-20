<?php
/**
 * Created by IntelliJ IDEA.
 * User: yanoshin
 * Date: 6/20/15
 * Time: 13:30
 */


class XbrlSearchDlInfo
{
    private $document_code;
    private $title;
    private $edinet_code;
    private $publisher;
    private $publisher_name;
    private $publisher_ticker;
    private $publisher_otc;
    private $publisher_stock_listing;
    private $investor_genre;
    private $investor_name;
    private $investor_address;

    /**
     * @return mixed
     */
    public function getInvestorAddress()
    {
        return $this->investor_address;
    }

    /**
     * @param mixed $investor_address
     */
    public function setInvestorAddress($investor_address)
    {
        $this->investor_address = $investor_address;
    }

    /**
     * @return mixed
     */
    public function getInvestorGenre()
    {
        return $this->investor_genre;
    }

    /**
     * @param mixed $investor_genre
     */
    public function setInvestorGenre($investor_genre)
    {
        $this->investor_genre = $investor_genre;
    }

    /**
     * @return mixed
     */
    public function getInvestorName()
    {
        return $this->investor_name;
    }

    /**
     * @param mixed $investor_name
     */
    public function setInvestorName($investor_name)
    {
        $this->investor_name = $investor_name;
    }

    /**
     * @return mixed
     */
    public function getPublisherName()
    {
        return $this->publisher_name;
    }

    /**
     * @param mixed $publisher_name
     */
    public function setPublisherName($publisher_name)
    {
        $this->publisher_name = $publisher_name;
    }

    /**
     * @return mixed
     */
    public function getPublisherOtc()
    {
        return $this->publisher_otc;
    }

    /**
     * @param mixed $publisher_otc
     */
    public function setPublisherOtc($publisher_otc)
    {
        $this->publisher_otc = $publisher_otc;
    }

    /**
     * @return mixed
     */
    public function getPublisherStockListing()
    {
        return $this->publisher_stock_listing;
    }

    /**
     * @param mixed $publisher_stock_listing
     */
    public function setPublisherStockListing($publisher_stock_listing)
    {
        $this->publisher_stock_listing = $publisher_stock_listing;
    }

    /**
     * @return mixed
     */
    public function getPublisherTicker()
    {
        return $this->publisher_ticker;
    }

    /**
     * @param mixed $publisher_ticker
     */
    public function setPublisherTicker($publisher_ticker)
    {
        $this->publisher_ticker = $publisher_ticker;
    }


    /**
     * @return mixed
     */
    public function getDocumentCode()
    {
        return $this->document_code;
    }

    /**
     * @param mixed $document_code
     */
    public function setDocumentCode($document_code)
    {
        $this->document_code = $document_code;
    }

    /**
     * @return mixed
     */
    public function getEdinetCode()
    {
        return $this->edinet_code;
    }

    /**
     * @param mixed $edinet_code
     */
    public function setEdinetCode($edinet_code)
    {
        $this->edinet_code = $edinet_code;
    }

    /**
     * @return mixed
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param mixed $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }



}