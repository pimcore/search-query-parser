<?php

declare(strict_types=1);

namespace SearchQueryParser;

final class Token
{
    /**
     * @var int
     */
    private $token;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $content;

    /**
     * @param int $token
     * @param int $line
     * @param string $content
     */
    public function __construct(int $token, int $line, string $content)
    {
        $this->token   = $token;
        $this->line    = $line;
        $this->content = $content;
        $this->name    = Tokens::getName($token);
    }

    /**
     * @return int
     */
    public function getToken(): int
    {
        return $this->token;
    }

    /**
     * @param int|array $token
     *
     * @return bool
     */
    public function isTypeOf($token): bool
    {
        if (!is_array($token)) {
            $token = [$token];
        }

        return in_array($this->token, $token);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
