<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'towar')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'TowId', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'AsId', type: Types::INTEGER)]
    private ?int $AsId = null;

    #[ORM\Column(name: 'JMId', type: Types::INTEGER)]
    private ?int $JMId = null;

    #[ORM\Column(name: 'CenaDet', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $Price = null;

    #[ORM\Column(name: 'Producent', type: Types::INTEGER, nullable: true)]
    private ?int $Producent = null;

    #[ORM\Column(name: 'ArtId', type: Types::INTEGER, nullable: true)]
    private ?int $ArtId = null;

    #[ORM\Column(name: 'promotion_enabled', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $PromotionEnabled = false;

    #[ORM\Column(name: 'promotion_percent', nullable: true)]
    private ?int $PromotionPercent = null;

    #[ORM\Column(name: 'image', length: 255, nullable: true)]
    private ?string $Image = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(name: 'stock', nullable: true)]
    private ?int $Stock = null;

    #[ORM\Column(name: 'Nazwa', length: 255)]
    private ?string $Name = null;

    #[ORM\Column(name: 'Skrot', length: 120)]
    private ?string $Skrot = null;

    #[ORM\Column(name: 'Kod', length: 20)]
    private ?string $Kod = null;

    #[ORM\Column(name: 'TypTowaru', type: Types::SMALLINT)]
    private ?int $TypTowaru = null;

    #[ORM\Column(name: 'Indeks1', length: 40)]
    private ?string $Indeks1 = null;

    #[ORM\Column(name: 'Indeks2', length: 40)]
    private ?string $Indeks2 = null;

    #[ORM\Column(name: 'Opis1', length: 60)]
    private ?string $Opis1 = null;

    #[ORM\Column(name: 'Opis2', length: 60)]
    private ?string $Opis2 = null;

    #[ORM\Column(name: 'Opis3', length: 60)]
    private ?string $Opis3 = null;

    #[ORM\Column(name: 'Opis4', length: 60)]
    private ?string $Opis4 = null;

    #[ORM\Column(name: 'TermWazn', type: Types::SMALLINT)]
    private ?int $TermWazn = null;

    #[ORM\Column(name: 'Marza', type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $Marza = null;

    #[ORM\Column(name: 'HurtRabat', type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $HurtRabat = null;

    #[ORM\Column(name: 'NocNarzut', type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $NocNarzut = null;

    #[ORM\Column(name: 'OpcjaMarzy', type: Types::SMALLINT)]
    private ?int $OpcjaMarzy = null;

    #[ORM\Column(name: 'OpcjaRabatu', type: Types::SMALLINT)]
    private ?int $OpcjaRabatu = null;

    #[ORM\Column(name: 'OpcjaNarzutu', type: Types::SMALLINT)]
    private ?int $OpcjaNarzutu = null;

    #[ORM\Column(name: 'CenaEw', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $CenaEw = null;

    #[ORM\Column(name: 'CenaHurt', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $CenaHurt = null;

    #[ORM\Column(name: 'CenaNoc', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $CenaNoc = null;

    #[ORM\Column(name: 'CenaDod', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $CenaDod = null;

    #[ORM\Column(name: 'CenaOtwarta', type: Types::SMALLINT)]
    private ?int $CenaOtwarta = null;

    #[ORM\Column(name: 'PoziomCen', type: Types::SMALLINT)]
    private ?int $PoziomCen = null;

    #[ORM\Column(name: 'PrefPLU', type: Types::INTEGER)]
    private ?int $PrefPLU = null;

    #[ORM\Column(name: 'Stawka', type: Types::SMALLINT)]
    private ?int $Stawka = null;

    #[ORM\Column(name: 'IleWZgrzewce', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $IleWZgrzewce = null;

    #[ORM\Column(name: 'IleWCalosci', type: Types::DECIMAL, precision: 15, scale: 4)]
    private ?string $IleWCalosci = null;

    #[ORM\Column(name: 'KodZgrzewki', length: 20)]
    private ?string $KodZgrzewki = null;

    #[ORM\Column(name: 'Aktywny', type: Types::SMALLINT)]
    private ?int $Aktywny = null;

    #[ORM\Column(name: 'Waga', type: Types::INTEGER)]
    private ?int $Waga = null;

    #[ORM\Column(name: 'Szerokosc', type: Types::INTEGER)]
    private ?int $Szerokosc = null;

    #[ORM\Column(name: 'Wysokosc', type: Types::INTEGER)]
    private ?int $Wysokosc = null;

    #[ORM\Column(name: 'Glebokosc', type: Types::INTEGER)]
    private ?int $Glebokosc = null;

    #[ORM\Column(name: 'CKU', length: 20)]
    private ?string $CKU = null;

    #[ORM\Column(name: 'BlokDostawcow', type: Types::SMALLINT)]
    private ?int $BlokDostawcow = null;

    #[ORM\Column(name: 'BlokCenyZak', type: Types::SMALLINT)]
    private ?int $BlokCenyZak = null;

    #[ORM\Column(name: 'BlokCenSp', type: Types::SMALLINT)]
    private ?int $BlokCenSp = null;

    #[ORM\Column(name: 'BlokZmian', type: Types::SMALLINT)]
    private ?int $BlokZmian = null;

    #[ORM\Column(name: 'Rezerwa1', length: 40)]
    private ?string $Rezerwa1 = null;

    #[ORM\Column(name: 'Rezerwa2', length: 40)]
    private ?string $Rezerwa2 = null;

    #[ORM\Column(name: 'CentrTowId', type: Types::INTEGER, nullable: true)]
    private ?int $CentrTowId = null;

    #[ORM\Column(name: 'Zmiana', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Zmiana = null;

    #[ORM\Column(name: 'Akcyzowy', type: Types::SMALLINT, nullable: true)]
    private ?int $Akcyzowy = null;

    #[ORM\Column(name: 'SledzPartii', type: Types::SMALLINT, nullable: true)]
    private ?int $SledzPartii = null;

    #[ORM\Column(name: 'MaxCenaZak', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
    private ?string $MaxCenaZak = null;

    #[ORM\Column(name: 'PrzeliczJM', length: 20, nullable: true)]
    private ?string $PrzeliczJM = null;

    #[ORM\Column(name: 'NrDrukarki', type: Types::SMALLINT, nullable: true)]
    private ?int $NrDrukarki = null;

    #[ORM\Column(name: 'Cena5', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
    private ?string $Cena5 = null;

    #[ORM\Column(name: 'Cena6', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
    private ?string $Cena6 = null;

    #[ORM\Column(name: 'ProgPromocji', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
    private ?string $ProgPromocji = null;

    #[ORM\Column(name: 'ZmianaIstotna', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ZmianaIstotna = null;

    #[ORM\Column(name: 'ZmianaTylkoCen', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ZmianaTylkoCen = null;

    #[ORM\Column(name: 'Przeznaczenie', type: Types::SMALLINT, nullable: true)]
    private ?int $Przeznaczenie = null;

    #[ORM\Column(name: 'ObslugaPartii', type: Types::SMALLINT, nullable: true)]
    private ?int $ObslugaPartii = null;

    #[ORM\Column(name: 'UkrycNaKasie', type: Types::SMALLINT, nullable: true)]
    private ?int $UkrycNaKasie = null;

    #[ORM\Column(name: 'KodCN', length: 20, nullable: true)]
    private ?string $KodCN = null;

    #[ORM\Column(name: 'MinCenaSp', type: Types::DECIMAL, precision: 15, scale: 4, nullable: true)]
    private ?string $MinCenaSp = null;

    #[ORM\Column(name: 'SubsysKoduGlownego', length: 40, nullable: true)]
    private ?string $SubsysKoduGlownego = null;

    #[ORM\Column(name: 'StatusZam', type: Types::SMALLINT, nullable: true)]
    private ?int $StatusZam = null;

    #[ORM\Column(name: 'KodGlownyCentralny', type: Types::SMALLINT, nullable: true)]
    private ?int $KodGlownyCentralny = null;

    #[ORM\Column(name: 'NowoscOd', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $NowoscOd = null;

    #[ORM\Column(name: 'NowoscPrzez', type: Types::SMALLINT, nullable: true)]
    private ?int $NowoscPrzez = null;

    #[ORM\Column(name: 'WysylacNaSklepInternetowy', type: Types::SMALLINT, nullable: true)]
    private ?int $WysylacNaSklepInternetowy = null;

    #[ORM\Column(name: 'GrupaGTU', length: 3, nullable: true)]
    private ?string $GrupaGTU = null;

    #[ORM\Column(name: 'KrajIdPochodzenia', type: Types::INTEGER, nullable: true)]
    private ?int $KrajIdPochodzenia = null;

    #[ORM\Column(name: 'Zywnosc', type: Types::SMALLINT, nullable: true)]
    private ?int $Zywnosc = null;

    #[ORM\Column(name: 'KodSklepu', length: 30, nullable: true)]
    private ?string $KodSklepu = null;

    #[ORM\Column(name: 'FrakId', type: Types::INTEGER, nullable: true)]
    private ?int $FrakId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'KatId', referencedColumnName: 'KatId', nullable: true, onDelete: 'SET NULL')]
    private ?Category $Category = null;

	/** @var Collection<int, Opinion> */
	#[ORM\OneToMany(mappedBy: 'Product', targetEntity: Opinion::class, orphanRemoval: true)]
	private Collection $Opinions;

	public function __construct()
	{
		$this->Opinions = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->Price !== null ? (float) $this->Price : null;
    }

    public function setPrice(float $Price): static
    {
        $this->Price = number_format($Price, 4, '.', '');

        return $this;
    }

	public function isPromotionEnabled(): bool
	{
		return $this->PromotionEnabled;
	}

	public function setPromotionEnabled(bool $PromotionEnabled): static
	{
		$this->PromotionEnabled = $PromotionEnabled;

		return $this;
	}

	public function getPromotionPercent(): ?int
	{
		return $this->PromotionPercent;
	}

	public function setPromotionPercent(?int $PromotionPercent): static
	{
		if ($PromotionPercent === null) {
			$this->PromotionPercent = null;
			return $this;
		}

		$this->PromotionPercent = max(0, min(99, $PromotionPercent));
		return $this;
	}

	public function hasPromotion(): bool
	{
		return $this->PromotionEnabled
			&& $this->PromotionPercent !== null
			&& $this->PromotionPercent > 0;
	}

	public function getEffectivePrice(): float
	{
		$basePrice = (float) ($this->Price ?? 0);

		if (!$this->hasPromotion()) {
			return $basePrice;
		}

		$discounted = $basePrice * (100 - (int) $this->PromotionPercent) / 100;
		return round($discounted, 2);
	}

    public function getImage(): ?string
    {
        return $this->Image;
    }

    public function setImage(?string $Image): static
    {
        $this->Image = $Image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->Stock;
    }

    public function setStock(?int $Stock): static
    {
        $this->Stock = $Stock;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->Category;
    }

    public function setCategory(?Category $Category): static
    {
        $this->Category = $Category;

        return $this;
    }

	/** @return Collection<int, Opinion> */
	public function getOpinions(): Collection
	{
		return $this->Opinions;
	}

	public function addOpinion(Opinion $Opinion): static
	{
		if (!$this->Opinions->contains($Opinion)) {
			$this->Opinions->add($Opinion);
			$Opinion->setProduct($this);
		}

		return $this;
	}

	public function removeOpinion(Opinion $Opinion): static
	{
		if ($this->Opinions->removeElement($Opinion)) {
			if ($Opinion->getProduct() === $this) {
				$Opinion->setProduct(null);
			}
		}

		return $this;
	}
}
