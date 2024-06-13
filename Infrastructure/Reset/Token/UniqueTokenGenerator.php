<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Aropixel\AdminBundle\Infrastructure\Reset\Token;

use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Infrastructure\Reset\Token\GeneratorInterface;
use Aropixel\AdminBundle\Infrastructure\Reset\Token\RandomnessGeneratorInterface;


final class UniqueTokenGenerator implements GeneratorInterface
{

    private RandomnessGeneratorInterface $generator;
    private UserRepositoryInterface $userRepository;


    private int $tokenLength = 16;

    /**
     * @param RandomnessGeneratorInterface $generator
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(RandomnessGeneratorInterface $generator, UserRepositoryInterface $userRepository)
    {
        $this->generator = $generator;
        $this->userRepository = $userRepository;
    }


    /**
     * {@inheritdoc}
     */
    public function generate(): string
    {
        do {
            $token = $this->generator->generateUriSafeString($this->tokenLength);
        } while (!$this->isUnique($token));

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique(string $token): bool
    {
        return null === $this->userRepository->findOneBy(['passwordResetToken' => $token]);
    }
}
