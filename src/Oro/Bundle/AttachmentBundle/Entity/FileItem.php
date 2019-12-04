<?php

namespace Oro\Bundle\AttachmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AttachmentBundle\Model\ExtendFileItem;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Table(name="oro_attachment_file_item")
 * @ORM\Entity()
 * @Config
 */
class FileItem extends ExtendFileItem
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var File|null
     *
     * @ORM\OneToOne(
     *     targetEntity="Oro\Bundle\AttachmentBundle\Entity\File",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     *  )
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $file;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_order", type="integer", options={"default"=0})
     */
    protected $sortOrder = 0;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param File $file
     * @return $this
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $order
     * @return $this
     */
    public function setSortOrder(int $order)
    {
        $this->sortOrder = $order;

        return $this;
    }
}
