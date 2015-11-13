<?php

namespace Kregel\Warden\Traits;

trait Wardenable
{
    /**
     * get the warden variable.
     *
     * @return array
     */
    public function getWarden()
    {
        return $this->warden;
    }

    /**
     * Set the variable warden on runtime and return this.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setWarden(Array $fields)
    {
        $this->warden = $fields;

        return $this;
    }

    /**
     * Parses the warden config.
     *
     * @return array
     */
    public function toArray()
    {
        $attr = $this->getWarden();
        $returnable = [];
        foreach ($attr as $old => $new) {
            if(stripos($old, '_id') !== false)
                $returnable[$new] = $this->$new;
            elseif(stripos($old, '_to') !== false){
                $a = json_decode($this->$old, true);
                $keys = array_keys($a);
                foreach($keys as $key){
                    foreach($a[$key] as $value){
                        $model = config('kregel.warden.models.' . $key . '.model');
                        $returnable[$old][] =$model::find($value);
                    }
                }
            } else
                $returnable[$new] = $this->$old;
        }
        return $returnable;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
