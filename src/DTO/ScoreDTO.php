<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ScoreDTO
{
    #[Assert\NotBlank(message: 'Оценка не указана')]
    #[Assert\Type(type: 'int', message: 'Оценка должна быть целым числом от 0 до 100')]
    #[Assert\Range(
        notInRangeMessage: 'Значение оценки должно быть в интервале от {{ min }} до {{ max }}',
        min: 0,
        max: 100,
    )]
    public int $score;

    public function __construct(int $score)
    {
        $this->score = $score;
    }
}