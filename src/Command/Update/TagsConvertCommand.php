<?php declare(strict_types=1);

namespace App\Command\Update;

use App\Entity\Entry;
use App\Entity\EntryComment;
use App\Entity\Post;
use App\Entity\PostComment;
use App\Service\TagManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagsConvertCommand extends Command
{
    protected static $defaultName = 'kbin:tags:convert';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows refresh entries tags.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entries = $this->entityManager->getRepository(Entry::class)->findAll();
        foreach ($entries as $entry) {
            if(null === $entry->tags_tmp || !count($entry->tags_tmp)) {
                continue;
            }

            $entry->tags = array_values($entry->tags_tmp);
            $this->entityManager->persist($entry);
        }

        $comments = $this->entityManager->getRepository(EntryComment::class)->findAll();
        foreach ($comments as $comment) {
            if(null === $comment->tags_tmp || !count($comment->tags_tmp)) {
                continue;
            }

            $comment->tags = array_values($comment->tags_tmp);
            $this->entityManager->persist($comment);
        }

        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        foreach ($posts as $post) {
            if(null === $post->tags_tmp || !count($post->tags_tmp)) {
                continue;
            }

            $post->tags = array_values($post->tags_tmp);
            $this->entityManager->persist($post);
        }

        $comments = $this->entityManager->getRepository(PostComment::class)->findAll();
        foreach ($comments as $comment) {
            if(null === $comment->tags_tmp || !count($comment->tags_tmp)) {
                continue;
            }

            $comment->tags = array_values($comment->tags_tmp);
            $this->entityManager->persist($comment);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
