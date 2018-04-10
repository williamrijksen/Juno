<?php

namespace Juno;

class EnvelopeIterator implements \Iterator
{
    protected $envelopes;

    public function __construct(array $envelopes)
    {
        $this->envelopes = $envelopes;
    }

    public function current()
    {
        $envelope = current($this->envelopes);

        return array(
            'timestamp' => $envelope->getTimestamp(),
            'class'     => $envelope->getClass(),
            'name'      => $envelope->getName(),
            'arguments' => [
                'name' => $envelope->getMessage()->getName(),
                'arguments' => $envelope->getMessage()->all()
            ],
        );
    }

    public function next()
    {
        next($this->envelopes);
    }

    public function key()
    {
        return key($this->envelopes);
    }

    public function valid()
    {
        return isset($this->envelopes[$this->key()]);
    }

    public function rewind()
    {
        reset($this->envelopes);
    }
}
