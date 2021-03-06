<?php

namespace UoBParser\Entities;

use \Exception;

class Course
{
    public $id;
    public $name;
    public $level;
    public $deptId;
    public $department;

    function __construct($id, $name, $level, $deptId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->deptId = $deptId;
        $this->department = false;
    }

    /**
     * Get the url to access the sessions for this course
     * @return string
     */
    public function sessionUrl()
    {
        $args['dept'] = $this->deptId;
        $args['course'] = $this->id;
        $args['level'] = $this->level;

        $isHttps = empty($_SERVER['HTTPS']) == false && $_SERVER['HTTPS'] != 'off';

        $url = $isHttps ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['SERVER_PORT'] != '80' ? ':' . $_SERVER['SERVER_PORT'] : '';
        $url .= dirname($_SERVER['PHP_SELF']);
        $url .= '/sessions?'.http_build_query($args);

        return $url;
    }

    /**
     * Split name in to chunks
     * Parse the string so that it isn't split by '-' between brackets
     * @return array
     */
    public function nameChunks()
    {
        $name = $this->name;

        $chunks = [];
        $bracketLevel = 0;
        $lastSectionStart = 0;
        foreach (str_split($name) as $index => $char) {
            $atEnd = $index == strlen($name) -1;
            
            if ($char == '(') {
                $bracketLevel += 1;
            } else if ($char == ')') {
                $bracketLevel -= 1;
            } else if (($char == '-' || $atEnd) && $bracketLevel == 0){
                if ($atEnd)
                    $chunk = substr($name, $lastSectionStart);
                else
                    $chunk = substr($name, $lastSectionStart, $index - $lastSectionStart);
                $chunk = trim($chunk, '- ');    
                    
                $chunks[] = $chunk;
                $lastSectionStart = $index;
            }
        }

        if (count($chunks) == 0)
            return [$name];

        return $chunks;
    }

    /**
     * Return an array containing:
     *  - name_start - name of the course
     *  - name_end - all other data (year etc)
     * @return array
     */
    public function names()
    {
        $nameChunks = $this->nameChunks();
       
        $names['name_start'] = $nameChunks[0];
        $names['name_end'] = implode(' - ', array_slice($nameChunks, 1));

        return $names;
    }

    /**
     * Get an array representing this object suitable for serialisation.
     * @return array
     */
    public function toArray()
    {
        $data = [
            'id'            => $this->id,
            'name'          => $this->name,
            'level'         => $this->level,
            'department'    => $this->department ? $this->department->toArray() : null,
            'session_url'   => $this->sessionUrl(),

        ];

        return array_merge($data, $this->names());
    }

}