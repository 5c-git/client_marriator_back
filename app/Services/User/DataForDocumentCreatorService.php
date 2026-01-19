<?php

namespace App\Services\User;

use App\Enum\Document\DocumentTemplates\DocumentTemplatesEnum;
use App\Enum\Fields\FieldsTypeEnum;
use App\Models\Document\Document;
use App\Models\Document\DocumentTemplate;
use App\Models\Fields\Directory\Counterparty;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Fields;
use App\Models\Order\Bid;
use App\Models\Order\OrderInterface;
use App\Models\Order\Report;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class DataForDocumentCreatorService
{
    private array $moreInfo = [];
    private array $moreInfoField = [];
    protected array $settings = [];
    private User $user;
    private Counterparty $counterparty;
    private UserDataService $userDataService;
    private ?OrderInterface $order;
    private ?DocumentTemplate $offer = null;
    private ?DocumentTemplate $offerSelf = null;

    public function __construct(User $user, Counterparty $counterparty,?OrderInterface $order = null)
    {
        $this->user = $user;
        $this->counterparty = $counterparty;
        $this->userDataService = new UserDataService();
        $this->order = $order;
    }

    public function getName()
    {
        return $this->userDataService->getOnlyName($this->user);
    }

    public function getLastName()
    {
        return $this->userDataService->getOnlyLastName($this->user);
    }

    public function getSecondName()
    {
        return $this->userDataService->getOnlySecondName($this->user);
    }

    public function getTotalAmount():string
    {
        return '4';
    }

    public function getAdditionalAgreementCode():string
    {
        return Document::max('id')??'1';
    }

    public function getEmployedContractNumber():string
    {
        return 'не заполнено';
    }

    public function getOfferAgreementName():string
    {
        if(!$this->offer){
            $this->getOffer();
        }

        if($this->offer){
            return $this->offer->number;
        }
        return 'не заполнено';
    }

    public function getEmployedOfferName():string
    {
        if(!$this->offerSelf){
            $this->getOfferSelf();
        }

        if($this->offerSelf){
            return $this->offerSelf->number;
        }
        return 'не заполнено';
    }

    public function getOfferAgreementRevision()
    {
        if(!$this->offer){
            $this->getOffer();
        }

        if($this->offer){
            return $this->offer->date_start->format('d-m-Y');
        }
        return 'не заполнено';
    }

    public function getEmployedOfferRevision()
    {
        if(!$this->offerSelf){
            $this->getOfferSelf();
        }

        if($this->offerSelf){
            return $this->offerSelf->date_start->format('d-m-Y');
        }
        return 'не заполнено';
    }

    public function getOfferSigningPlace()
    {
        if(!$this->offer){
            $this->getOffer();
        }

        if($this->offer){
            return $this->offer->place;
        }
        return 'не заполнено';
    }

    public function getAgreementSigningDate()
    {
        return Carbon::now()->format('Y');
    }

    public function getCurrentDate()
    {
        return Carbon::now()->format('d.m.Y');
    }

    public function getCounterpartyFullName(): string
    {
        return $this->counterparty->name??'не заполнено';
    }

    public function getSignatoryPosition(): string
    {
        return $this->counterparty->position??'не заполнено';
    }

    public function getSignatoryFullName(): string
    {
        return $this->counterparty->full_name??'не заполнено';
    }

    public function getDirectorShortName(): string
    {
        return $this->counterparty->full_name?$this->shortenFIO($this->counterparty->full_name):'не заполнено';
    }

    public function getSigningAuthority(): string
    {
        return 'не заполнено';
    }

    public function getIndividualFirstName(): string
    {
        return $this->userDataService->getOnlyName($this->user)??'не заполнено';
    }

    public function getIndividualGender(): string
    {
        return $this->userDataService->getGender($this->user)??'не заполнено';
    }

    public function getIndividualBirthDate(): string
    {
        return $this->userDataService->getBirthDate($this->user)??'не заполнено';
    }

    public function getBirthYear(): string
    {
        return $this->userDataService->getBirthDateYear($this->user)??'не заполнено';
    }

    public function getCorporateWebsite(): string
    {
        return $this->counterparty->web??'не заполнено';
    }

    public function getLegalAddress(): string
    {
        return $this->counterparty->legal_address??'не заполнено';
    }

    public function getCounterpartyName(): string
    {
        return $this->counterparty->name??'не заполнено';
    }

    public function getMainBrandName(): string
    {
        return $this->counterparty->brand_name??'не заполнено';
    }

    public function getCounterpartyInn(): string
    {
        return $this->counterparty->inn??'не заполнено';
    }

    public function getCounterpartyKpp(): string
    {
        return $this->counterparty->kpp??'не заполнено';
    }

    public function getCounterpartyOgrn(): string
    {
        return $this->counterparty->ogrn??'не заполнено';
    }

    public function getMainBankName(): string
    {
        return $this->counterparty->bank_name??'не заполнено';
    }

    public function getBankAccountNumber(): string
    {
        return $this->counterparty->bank_account_number??'не заполнено';
    }

    public function getBankCorrAccount(): string
    {
        return $this->counterparty->bank_corr_account??'не заполнено';
    }

    public function getBankBic(): string
    {
        return $this->counterparty->bank_bic??'не заполнено';
    }

    public function getCounterpartyOkpo(): string
    {
        return $this->counterparty->okpo??'не заполнено';
    }

    public function getCounterpartyOkved(): string
    {
        return $this->counterparty->okved??'не заполнено';
    }

    public function getCounterpartyPhone(): string
    {
        return $this->counterparty->phone??'не заполнено';
    }

    public function getCounterpartyEmail(): string
    {
        return $this->counterparty->legal_email??'не заполнено';
    }

    public function getIndividualLastName(): string
    {
        return $this->userDataService->getOnlyLastName($this->user)??'не заполнено';
    }

    public function getIndividualMiddleName(): string
    {
        return $this->userDataService->getOnlySecondName($this->user)??'не заполнено';
    }

    public function getSpecialistFullName(): string
    {
        return $this->userDataService->getName($this->user)??'не заполнено';
    }

    public function getPassportDetails(): string
    {
        return $this->userDataService->getPassportDetails($this->user)??'не заполнено';
    }

    public function getPassportIssuer(): string
    {
        return $this->userDataService->getPassportDetails($this->user)??'не заполнено';
    }

    public function getRegistrationAddress(): string
    {
        return $this->userDataService->getRegistrationAddress($this->user)??'не заполнено';
    }

    public function getWorkPermitDetails(): string
    {
        return 'не заполнено';
    }

    public function getCitizenshipDisplay(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
               return $this->order->viewActivity?->name??'не заполнено';
            }
        }
        return 'не заполнено';
    }

    public function getServiceBaseNorm(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return $this->order->viewActivity?->standart?->name??'не заполнено';
            }
        }
        return 'не заполнено';
    }

    public function getIncomeWithTax(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return $this->getForPay($this->getReports($this->order->acceptingUsers,$this->order->id));
            }
        }
        return 'не заполнено';
    }

    public function getIdentityDocumentType(): string
    {
        return $this->userDataService->getIsHavePassport($this->user)? 'Паспорт или его эквивалент':'не заполнено';
    }

    public function getSpecialRequirements(): string
    {
        return 'не требуется';
    }

    public function getSignatoryShortName(): string
    {
        return $this->userDataService->getShortName($this->user)?? 'не заполнено';
    }

    public function getIndividualNameLetters(): string
    {
        return $this->userDataService->getShortNameOnliLaters($this->user)?? 'не заполнено';
    }

    public function getIndividualMobilePhone(): string
    {
        return $this->user->phone?? 'не заполнено';
    }

    public function getEmployedSigningBasis(): string
    {
        return 'не заполнено';
    }

    public function getEmployedFromDate(): string
    {
        return 'не заполнено';
    }

    public function getPrimaryOfferName(): string
    {
        return 'не заполнено';
    }

    public function getTaxStatusName(): string
    {
        return $this->userDataService->getTaxStatusName($this->user)?? 'не заполнено';
    }

    public function getServiceExecutor(): string
    {
        return $this->userDataService->getShortNameOnliLatersWithLastName($this->user)?? 'не заполнено';
    }

    public function getInstructorName(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
               return (new UserDataService())->getName($this->order->user);
            }
        }
        return 'не заполнено';
    }

    public function getInstructionLocation(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return $this->order->place?->name;
            }
        }
        return 'не заполнено';
    }

    public function getRequestSequenceNumber(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return (string)$this->order->id;
            }
        }
        return 'не заполнено';
    }

    public function getSafetyInstructionCode(): string
    {
        return $this->counterparty->id.'не заполнено'.$this->user->id.'-ТБ-ВИ';
    }

    public function getTaxReferenceLink(): string
    {
        return Setting::getValue('taxReferenceLink')??'не заполнено';
    }

    public function getServiceResult(): string
    {
        return 'не заполнено';
    }

    public function getServiceTimeliness(): string
    {
        return 'не заполнено';
    }

    public function getServiceComposition(): string
    {
        return 'не заполнено';
    }

    public function getServiceQuality(): string
    {
        return 'не заполнено';
    }

    public function getServiceRequirements(): string
    {
        return 'не заполнено';
    }

    public function getServicePrice(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return (string)$this->order->price;
            }
        }
        return 'не заполнено';
    }

    public function getServiceStartDate(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return (string)$this->order->date_start->format('d.m.Y');
            }
        }
        return 'не заполнено';
    }
    public function getServiceEndDate(): string
    {
        if(!empty($this->order)){
            if($this->order instanceof Bid){
                return (string)$this->order->date_end->format('d.m.Y');
            }
        }
        return 'не заполнено';
    }

    private function getOffer(): void
    {
        $this->offer = DocumentTemplate::query()
            ->where('type', DocumentTemplatesEnum::offer->value)
            ->where('date_start', '<=', Carbon::now())
            ->where('date_end', '>=', Carbon::now())
            ->first()??null;
    }

    private function getOfferSelf(): void
    {
        $this->offerSelf = DocumentTemplate::query()
            ->where('type', DocumentTemplatesEnum::offerSelf->value)
            ->where('date_start', '<=', Carbon::now())
            ->where('date_end', '>=', Carbon::now())
            ->first()??null;
    }

    private function getForPay($reports = null): float|int
    {
        $forPay = 0;
        if($reports) {
            foreach ($reports as $report) {
                /** @var  $report Report */
                $forPay += $report->forPay + $report->getReasonsAmount();
            }
        }
        return $forPay;
    }

    private function getReports($users,int $bidId)
    {
        foreach ($users as $user){
            /** @var  $user User */
            if($user->id === $this->user->id){
                return $user->reports()?->where('bid_id',$bidId)->get();
            }
        }
    }

    private function shortenFIO(string $fullName): string
    {
        // Разбиваем строку на части
        $parts = explode(' ', trim($fullName));

        // Проверяем, что у нас есть все три части
        if (count($parts) >= 3) {
            // Берем фамилию как есть
            $surname = $parts[0];
            // Берем первую букву имени и добавляем точку
            $initials = mb_substr($parts[1], 0, 1) . '.';
            // Берем первую букву отчества и добавляем точку
            $initials .= mb_substr($parts[2], 0, 1) . '.';

            return $surname . ' ' . $initials;
        }

        // Если частей меньше 3, возвращаем исходную строку
        return $fullName;
    }
}
