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

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Generator\RandomnessGeneratorInterface;
use Aropixel\AdminBundle\Repository\UserRepository;
use Webmozart\Assert\Assert;

final class UniqueTokenGenerator implements GeneratorInterface
{
    /** @var RandomnessGeneratorInterface */
    private $generator;

    /** @var UserRepository */
    private $userRepository;

    /** @var int */
    private $tokenLength;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(
        RandomnessGeneratorInterface $generator,
        UserRepository $userRepository
    ) {
        $this->generator = $generator;
        $this->userRepository = $userRepository;
        $this->tokenLength = 16;
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
