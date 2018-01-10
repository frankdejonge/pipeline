<?php

namespace League\Pipeline;

use InvalidArgumentException;

class Pipeline implements PipelineInterface
{
    /**
     * @var callable[]
     */
    protected $stages = [];

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * Constructor.
     *
     * @param callable[]         $stages
     * @param ProcessorInterface $processor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $stages = [], ProcessorInterface $processor = null)
    {
        foreach ($stages as $stage) {
            if (false === is_callable($stage)) {
                throw new InvalidArgumentException('All stages should be callable.');
            }
        }

        $this->stages = $stages;
        $this->processor = $processor ?: new FingersCrossedProcessor;
    }

    /**
     * @inheritdoc
     */
    public function pipe(callable $stage)
    {
        $pipeline = clone $this;
        $pipeline->stages[] = $stage;

        return $pipeline;
    }

    /**
     * Process the payload.
     *
     * @param $payload
     *
     * @return mixed
     */
    public function process($payload)
    {
        return $this->processor->process($this->stages, $payload);
    }

    /**
     * Fork the pipeline
     *
     * @param callable|null $resolver
     *
     * @return Fork
     */
    public function fork(callable $resolver = null)
    {
        $fork = new Fork($resolver);
        $pipeline = $this->pipe($fork);
        $fork->pipeline($pipeline);
        return $fork;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($payload)
    {
        return $this->process($payload);
    }
}
