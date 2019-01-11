<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Exception\ValidatorException;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PictureRepository")
 * @Vich\Uploadable()
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Picture
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="album_image", fileNameProperty="filename")
     * @Assert\Image(
     *     mimeTypes="image/jpeg"
     * )
     * @Assert\File(maxSize="1500k")
     */
    private $imageFile;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $info = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rotation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Album", inversedBy="pictures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $album;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $orientation;

    /**
     * @ORM\Column(type="integer")
     */
    private $largeur;

    /**
     * @ORM\Column(type="integer")
     */
    private $hauteur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $private;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slugImage;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Etiquette", inversedBy="pictures")
     */
    private $etiquettes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="picture", orphanRemoval=true)
     */
    private $comments;

    public function getAutorizeFile(){
       if ( ($this->getLargeur() > 1920 or $this->getHauteur() > 1920) ) {
            return false;
        }
       return true;
    }

    /**
     * Initialise le slug
     * @return void
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function initializeSlug() {
        $slugify = new Slugify();
        if ($this->title != ""){
            $this->slug = $slugify->slugify($this->title);
            $this->slugImage = $this->getAlbum()->getProjet()->getAuthor()->getUsername()."-".$this->id."-".$this->slug;

        } else {
            $this->title = "Photo";
            $this->slug = "photo";
            $this->slugImage = $this->getAlbum()->getProjet()->getAuthor()->getUsername()."-".$this->id."-".$this->slug;

        }
    }

//    /**
//     * Permet de forcer l'utilisateur à completer la fiche de la photo pour la rendre publique
//     * @ORM\PreUpdate()
//     */
//    public function forceInfoPhoto() {
//        if ($this->title == 'Photo') {
//            $this->private = true;
//            throw new ValidatorException("Veuillez modifier le nom de l'image. Merci,");
//        }
//        if (empty($this->getEtiquettes())) {
//            $this->private = true;
//            throw new ValidatorException("Veuillez ajouter des tags à votre image. Merci,");
//        }
//    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->etiquettes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->private = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getInfo(): ?array
    {
        return $this->info;
    }

    public function setInfo(?array $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): File
    {
        return $this->imageFile;
    }

    /**
     * @param File $imageFile
     * @return Picture
     * @throws \Exception
     */
    public function setImageFile(File $imageFile): self
    {
        if($imageFile != null){
            if (function_exists('exif_read_data') or !isset($info["APP13"]) or !isset($info["APP1"])) {
                $this->extractInfo($imageFile);
            }
            $this->setUpdatedAt(new \DateTime());
            $this->imageFile = $imageFile;
            $this->setLargeur(getimagesize($imageFile)[0]);
            $this->setHauteur(getimagesize($imageFile)[1]);
            if ( $this->getHauteur() <= $this->getLargeur() ) {
                $this->setOrientation("paysage");
            } else {
                $this->setOrientation("portrait");
            }
            return $this;
        }
        //dump($this->hauteur);die();
        return $this;
    }

    /**
     * Converti des DMS en Degré décimal
     * @param $deg
     * @param $min
     * @param $sec
     * @return float|int
     */
    function DMStoDD($deg,$min,$sec)
    {
        // Converting DMS ( Degrees / minutes / seconds ) to decimal format
        // $deg+((($min*60)+($sec))/3600)
        $min = $min * 60;
        $minSec = $min + $sec;
        $minSecDiv = $minSec / 3600;
        $result = $minSecDiv + $deg;
        return $result;
    }

    /**
     * @param File $imageFile
     * @return void
     */
    private function extractInfo(File $imageFile): void {
        $info = @exif_read_data($imageFile);
        $infoImage = [];
        if ( !empty($info["Make"])     ) $infoImage["Périphérique"] = $info["Make"];
        if ( !empty($info["Model"])    ) $infoImage["Modèle"] = $info["Model"];
        if ( !empty($info["DateTime"]) ) {
            $infoImage["Date"] = $this->transformeDateString($info["DateTime"]);
        }
        if ( !empty($info["FileSize"]) ) $infoImage["Taille"] = $info["FileSize"];
        if ( !empty($info["GPSLatitude"]) ) {
            $infoImage["Latitude"] = $this->recupGPSInfo($info["GPSLatitude"]);
        }
        if ( !empty($info["GPSLongitude"]) ) {
            $infoImage["Longitude"] = $this->recupGPSInfo($info["GPSLongitude"]);
        }
        if ( !empty($info["ExifImageWidth"])) $infoImage["Largeur originale"] = $info["ExifImageWidth"];
        if ( !empty($info["ExifImageLength"])) $infoImage["Hauteur originale"] = $info["ExifImageLength"];
        //dump($info);die();
        $this->setInfo($infoImage);
    }

    public function transformeDateString($stringDate){
        $dateEtHeure = explode(" ",$stringDate);
        $dateAMod = explode(":", $dateEtHeure[0]);
        $dateFr = "$dateAMod[2]/$dateAMod[1]/$dateAMod[0]";
        $dateEtHeureFr = "le $dateFr à $dateEtHeure[1]";
        return $dateEtHeureFr;
    }

    /**
     * Récupération des info GPS (DMS) de l'image et convertion en degré grâce à la méthode DMStoDD
     * @param array $tabGPS
     * @return float|int
     */
    private function recupGPSInfo(array $tabGPS): float {
        $d = explode('/', $tabGPS[0]);
        $m = explode('/', $tabGPS[1]);
        $s = explode('/', $tabGPS[2]);
        $sDiv = $s[0] / 10000;
        $deg = str_replace(',', '.', $d[0]);
        $min = str_replace(',', '.', $m[0]);
        return $this->DMStoDD($deg,$min,$sDiv);
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRotation(): ?string
    {
        return $this->rotation;
    }

    public function setRotation(?string $rotation): self
    {
        $this->rotation = $rotation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getOrientation(): ?string
    {
        return $this->orientation;
    }

    public function setOrientation(?string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getLargeur(): ?int
    {
        return $this->largeur;
    }

    public function setLargeur(int $largeur): self
    {
        $this->largeur = $largeur;

        return $this;
    }

    public function getHauteur(): ?int
    {
        return $this->hauteur;
    }

    public function setHauteur(int $hauteur): self
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Etiquette[]
     */
    public function getEtiquettes(): Collection
    {
        return $this->etiquettes;
    }

    public function addEtiquette(Etiquette $etiquette): self
    {
        if (!$this->etiquettes->contains($etiquette)) {
            $this->etiquettes[] = $etiquette;
        }

        return $this;
    }

    public function removeEtiquette(Etiquette $etiquette): self
    {
        if ($this->etiquettes->contains($etiquette)) {
            $this->etiquettes->removeElement($etiquette);
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getAvgRatings() {
        $sum = array_reduce($this->comments->toArray(), function ($total, $comment) {
            return $total + $comment->getRating();
        }, 0);
        if(count($this->comments) > 0) return $sum / count($this->comments);
        return 0;
    }

    /**
     * Permet de récupérer le commentaire d'un author par rapport à une image
     * @param User $author
     * @return mixed|null
     */
    public function getCommentFromAuthor(User $author){
        foreach ($this->comments as $comment){
            if($comment->getAuthor() === $author) return $comment;
        }
        return null;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPicture($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPicture() === $this) {
                $comment->setPicture(null);
            }
        }

        return $this;
    }

    public function getSlugImage(): ?string
    {
        return $this->slugImage;
    }

    public function setSlugImage(string $slugImage): self
    {
        $this->slugImage = $slugImage;

        return $this;
    }

}
