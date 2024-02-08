<?php
// if you want to json encode and echo this object as a response, it must be json serializable
class Event implements \JsonSerializable
{
    private array $events;
    private bool  $error;

    public function __construct()
    {
        $this->events = [];
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addEvent(string $event): void
    {
        $this->events[] = $event;
    }

    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    public function isError(): bool
    {
        return $this->error;
    }

    public function setError(bool $error): void
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}