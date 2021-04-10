<?php declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EntryCommentRepository;
use Symfony\Component\Form\FormInterface;
use App\PageView\EntryCommentPageView;
use App\Event\EntryHasBeenSeenEvent;
use App\Form\EntryArticleType;
use App\Service\EntryManager;
use App\Form\EntryLinkType;
use App\Entity\Magazine;
use App\DTO\EntryDto;
use App\Entity\Entry;

class EntryController extends AbstractController
{
    public function __construct(
        private EntryManager $manager,
    ) {
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     */
    public function single(
        Magazine $magazine,
        Entry $entry,
        ?string $sortBy,
        EntryCommentRepository $commentRepository,
        EventDispatcherInterface $eventDispatcher,
        Request $request
    ): Response {
        $criteria = new EntryCommentPageView($this->getPageNb($request));
        $criteria->showSortOption($sortBy);
        $criteria->entry = $entry;

        $comments = $commentRepository->findByCriteria($criteria);

        $commentRepository->hydrate(...$comments);
        $commentRepository->hydrateChildren(...$comments);

        $eventDispatcher->dispatch((new EntryHasBeenSeenEvent($entry)));

        return $this->render(
            'entry/single.html.twig',
            [
                'magazine' => $magazine,
                'comments' => $comments,
                'entry'    => $entry,
            ]
        );
    }

    /**
     * @IsGranted("ROLE_USER")
     */
    public function create(?Magazine $magazine, ?string $type, Request $request): Response
    {
        $dto           = new EntryDto();
        $dto->magazine = $magazine;

        $form = $this->createFormByType($dto, $type);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->manager->create($dto, $this->getUserOrThrow());

            return $this->redirectToEntry($entry);
        }

        return $this->render(
            $this->getTemplateName($type),
            [
                'magazine' => $magazine,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("edit", subject="entry")
     */
    public function edit(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $dto = $this->manager->createDto($entry);

        $form = $this->createFormByType($dto, $dto->getType());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry = $this->manager->edit($entry, $dto);

            return $this->redirectToEntry($entry);
        }

        return $this->render(
            $this->getTemplateName($dto->getType(), true),
            [
                'magazine' => $magazine,
                'entry'    => $entry,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("delete", subject="entry")
     */
    public function delete(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_delete', $request->request->get('token'));

        $this->manager->delete($entry, !$entry->isAuthor($this->getUserOrThrow()));

        return $this->redirectToMagazine($magazine);
    }

    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("entry", options={"mapping": {"entry_id": "id"}})
     *
     * @IsGranted("ROLE_USER")
     * @IsGranted("purge", subject="entry")
     */
    public function purge(Magazine $magazine, Entry $entry, Request $request): Response
    {
        $this->validateCsrf('entry_purge', $request->request->get('token'));

        $this->manager->purge($entry);

        return $this->redirectToMagazine($magazine);
    }

    private function createFormByType(EntryDto $dto, ?string $type): FormInterface
    {
        if (!$type || $type === Entry::ENTRY_TYPE_LINK) {
            return $this->createForm(EntryLinkType::class, $dto);
        }

        return $this->createForm(EntryArticleType::class, $dto);
    }

    private function getTemplateName(?string $type, ?bool $edit = false): string
    {
        $prefix = $edit ? 'edit' : 'create';

        if (!$type || $type === Entry::ENTRY_TYPE_LINK) {
            return "entry/{$prefix}_link.html.twig";
        }

        return "entry/{$prefix}_article.html.twig";
    }
}
