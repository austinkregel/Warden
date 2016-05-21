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
    public function setWarden(array $fields)
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
        $attr = !empty($this->getWarden()) ? $this->getWarden() : null;
        if (empty($attr)) {
            $attr = empty($this->getVisible()) ? $this->getFillable() : $this->getVisible();
        }
        $returnable = [];
        foreach ($attr as $old => $new) {
            if (stripos($old, '_id') !== false) {
                if (!empty($this->$new)) {
                    $returnable[$new] = $this->{trim($new, '_id')};
                }
            } elseif (stripos($old, '_to') !== false) {
                $a = json_decode($this->$old, true);
                $keys = array_keys($a);
                foreach ($keys as $key) {
                    foreach ($a[$key] as $value) {
                        $model = config('kregel.warden.models.'.$key.'.model');
                        $returnable[$old][] = $model::find($value);
                    }
                }
            } else {
                if (isset($this->$old)) {
                    $returnable[$new] = $this->$old;
                }
            }
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
