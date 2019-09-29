<?php namespace Sheba\Reports;

use Sheba\Reports\Exceptions\NotAssociativeArray;

class Report
{
    const VIEW_FILE = "generic_report";
    const VARIABLE_NAME = "report";

    private $title;
    private $rows = [];
    private $headers = [];

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Report
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     * @return Report
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return Report
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Set the rows and headers for the report.
     *
     * @param array $data Must be an associative array.
     * @return $this
     * @throws NotAssociativeArray
     */
    public function set(array $data)
    {
        if (empty($data)) return $this;
        if (!isAssoc($data[0])) throw new NotAssociativeArray();

        $this->setRows($data);
        $this->setHeaders(!empty($data) ? array_keys($data[0]) : []);
        return $this;
    }
}