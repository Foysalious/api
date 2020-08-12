<?php namespace Sheba\Reports;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Sheba\Reports\Exceptions\NotAssociativeArray;

abstract class Handler
{
    protected $name;
    protected $filename;
    protected $folder;
    protected $viewPath;
    protected $viewFileName;

    protected $data;

    /**
     * @param $name
     * @param $view
     * @param $data
     * @return $this
     * @throws NotAssociativeArray
     */
    public function make($name, $view, $data)
    {
        $this->setName($name);
        $this->setViewFile($view);
        $this->setData($data);
        return $this;
    }

    /**
     * Set the (name + sheet name + file name) for the excel
     *
     * @param string $name Anything
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->setFilenameWithDate($name);
        return $this;
    }

    /**
     * Dynamically set the file name prefixed with current date.
     *
     * @param string $name
     * @param string $separator = '_'
     * @return $this
     */
    public function setFilenameWithDate($name, $separator = '_')
    {
        $date = Carbon::now()->toDateString();
        $name = str_replace(' ', $separator, $name);
        $this->setFilename($date . $separator . $name . $separator . "Report");
        return $this;
    }

    /**
     * Set the file name.
     *
     * @param string $name
     * @return $this
     */
    public function setFilename($name)
    {
        $this->filename = ucfirst(strtolower($name));
        return $this;
    }

    /**
     * Set the name of the view file in the default folder.
     *
     * @param string $name
     * @return $this
     */
    public function setViewFile($name)
    {
        $this->setViewFileWithPath($this->getViewPath() . $name);
        return $this;
    }

    /**
     * @param $folder
     * @return $this
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * @param $view_path
     * @return $this
     */
    public function setViewPath($view_path)
    {
        $this->viewPath = $view_path;
        return $this;
    }

    abstract protected function getViewPath();

    /**
     * Set the name of the view file.
     *
     * @param string $name Fully qualified file name (with folder).
     * @return $this
     */
    public function setViewFileWithPath($name)
    {
        $this->viewFileName = $name;
        return $this;
    }

    /**
     * Push some data to the view.
     *
     * @param string $key Variable name for the view.
     * @param mixed $value Value against the variable.
     * @return $this
     */
    public function pushData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set data directly.
     *
     * @param array $data Must be an associative array, whose keys will be variables in the view.
     * @return $this
     * @throws NotAssociativeArray
     */
    public function setData(array $data)
    {
        if (!isAssoc($data)) throw new NotAssociativeArray();
        $this->data = $data;
        return $this;
    }

    /**
     * Show the data in the loaded view.
     *
     * @return Response
     */
    public function show()
    {
        return view($this->viewFileName, $this->data);
    }

    abstract public function download($mPdf=false);

    abstract public function save();
}
