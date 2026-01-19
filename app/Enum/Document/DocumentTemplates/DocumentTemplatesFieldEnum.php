<?php

namespace App\Enum\Document\DocumentTemplates;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentTemplatesFieldEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case name = 1;
    case lastName = 2;
    case secondName = 3;
    case totalAmount = 4;
    case additionalAgreementCode = 5;
    case offerAgreementName = 6;
    case offerAgreementRevision = 7;
    case offerSigningPlace = 8;
    case agreementSigningDate = 9;
    case counterpartyFullName = 10;
    case signatoryPosition = 11;
    case signatoryFullName = 12;
    case signingAuthority = 13;////??????
    case individualFirstName = 14;
    case individualGender = 15;
    case individualBirthDate = 16;
    case corporateWebsite = 17;
    case legalAddress = 18;
    case counterpartyName = 19;
    case mainBrandName = 20;
    case counterpartyInn = 21;
    case counterpartyKpp = 22;
    case counterpartyOgrn = 23;
    case mainBankName = 24;
    case bankAccountNumber = 25;
    case bankCorrAccount = 26;
    case bankBic = 27;
    case counterpartyOkpo = 28;
    case counterpartyOkved = 29;
    case counterpartyPhone = 30;
    case counterpartyEmail = 31;
    case individualLastName = 32;
    case individualMiddleName = 33;
    case passportDetails = 34;
    case passportIssuer = 35;
    case registrationAddress = 36;
    case workPermitDetails = 37; //?????
    case citizenshipDisplay = 38;
    case serviceBaseNorm = 39;
    case incomeWithTax = 40;
    case identityDocumentType = 41;
    case specialRequirements = 42; //?????
    case personalSkills = 43; //?????
    case signatoryShortName = 44;
    case individualNameLetters = 45;
    case individualMobilePhone = 46;
    case employedContractNumber = 47;//?????
    case employedOfferName = 48;
    case employedOfferRevision = 49;
    case employedSigningBasis = 50;
    case employedFromDate = 51;
    case safetyInstructionCode = 52;
    case currentDate = 53;
    case specialistFullName = 54;
    case birthYear = 55;
    case primaryOfferName = 56;
    case additionalOfferName = 57;
    case instructionLocation = 58;
    case instructorName = 59;
    case taxReferenceLink = 60;
    case directorShortName = 61;
    case requestSequenceNumber = 62;
    case taxStatusName = 63;
    case serviceResult = 64;
    case serviceComposition = 65;
    case serviceQuality = 66;
    case serviceTimeliness = 67;
    case serviceRequirements = 68;
    case servicePrice = 69;
    case serviceStartDate = 70;
    case serviceEndDate = 71;
    case serviceExecutor = 72;


    public function getValue(): string
    {
        return match($this)
        {
            self::name => 'getName',
            self::lastName => 'getLastName',
            self::secondName => 'getSecondName',
            self::totalAmount => 'getTotalAmount',
            self::additionalAgreementCode => 'getAdditionalAgreementCode',
            self::offerAgreementName => 'getOfferAgreementName',
            self::offerAgreementRevision => 'getOfferAgreementRevision',
            self::offerSigningPlace => 'getOfferSigningPlace',
            self::agreementSigningDate => 'getAgreementSigningDate',
            self::counterpartyFullName => 'getCounterpartyFullName',
            self::signatoryPosition => 'getSignatoryPosition',
            self::signatoryFullName => 'getSignatoryFullName',
            self::signingAuthority => 'getSigningAuthority',
            self::individualFirstName => 'getIndividualFirstName',
            self::individualGender => 'getIndividualGender',
            self::individualBirthDate => 'getIndividualBirthDate',
            self::corporateWebsite => 'getCorporateWebsite',
            self::legalAddress => 'getLegalAddress',
            self::counterpartyName => 'getCounterpartyName',
            self::mainBrandName => 'getMainBrandName',
            self::counterpartyInn => 'getCounterpartyInn',
            self::counterpartyKpp => 'getCounterpartyKpp',
            self::counterpartyOgrn => 'getCounterpartyOgrn',
            self::mainBankName => 'getMainBankName',
            self::bankAccountNumber => 'getBankAccountNumber',
            self::bankCorrAccount => 'getBankCorrAccount',
            self::bankBic => 'getBankBic',
            self::counterpartyOkpo => 'getCounterpartyOkpo',
            self::counterpartyOkved => 'getCounterpartyOkved',
            self::counterpartyPhone => 'getCounterpartyPhone',
            self::counterpartyEmail => 'getCounterpartyEmail',
            self::individualLastName => 'getIndividualLastName',
            self::individualMiddleName => 'getIndividualMiddleName',
            self::passportDetails => 'getPassportDetails',
            self::passportIssuer => 'getPassportIssuer',
            self::registrationAddress => 'getRegistrationAddress',
            self::workPermitDetails => 'getWorkPermitDetails',
            self::citizenshipDisplay => 'getCitizenshipDisplay',
            self::serviceBaseNorm => 'getServiceBaseNorm',
            self::incomeWithTax => 'getIncomeWithTax',
            self::identityDocumentType => 'getIdentityDocumentType',
            self::specialRequirements => 'getSpecialRequirements',
            self::personalSkills => 'getPersonalSkills',
            self::signatoryShortName => 'getSignatoryShortName',
            self::individualNameLetters => 'getIndividualNameLetters',
            self::individualMobilePhone => 'getIndividualMobilePhone',
            self::employedContractNumber => 'getEmployedContractNumber',
            self::employedOfferName => 'getEmployedOfferName',
            self::employedOfferRevision => 'getEmployedOfferRevision',
            self::employedSigningBasis => 'getEmployedSigningBasis',
            self::employedFromDate => 'getEmployedFromDate',
            self::safetyInstructionCode => 'getSafetyInstructionCode',
            self::currentDate => 'getCurrentDate',
            self::specialistFullName => 'getSpecialistFullName',
            self::birthYear => 'getBirthYear',
            self::primaryOfferName => 'getPrimaryOfferName',
            self::additionalOfferName => 'getAdditionalOfferName',
            self::instructionLocation => 'getInstructionLocation',
            self::instructorName => 'getInstructorName',
            self::taxReferenceLink => 'getTaxReferenceLink',
            self::directorShortName => 'getDirectorShortName',
            self::requestSequenceNumber => 'getRequestSequenceNumber',
            self::taxStatusName => 'getTaxStatusName',
            self::serviceResult => 'getServiceResult',
            self::serviceComposition => 'getServiceComposition',
            self::serviceQuality => 'getServiceQuality',
            self::serviceTimeliness => 'getServiceTimeliness',
            self::serviceRequirements => 'getServiceRequirements',
            self::servicePrice => 'getServicePrice',
            self::serviceStartDate => 'getServiceStartDate',
            self::serviceEndDate => 'getServiceEndDate',
            self::serviceExecutor => 'getServiceExecutor',
        };
    }

    public function fromBD(): bool
    {
        return match($this)
        {
            self::name => true,
            self::lastName => true,
            self::secondName => true,
            self::totalAmount => false,
            self::additionalAgreementCode => false,
            self::offerAgreementName => false,
            self::offerAgreementRevision => false,
            self::offerSigningPlace => false,
            self::agreementSigningDate => false,
            self::counterpartyFullName => true,
            self::signatoryPosition => true,
            self::signatoryFullName => true,
            self::signingAuthority => true,
            self::individualFirstName => true,
            self::individualGender => true,
            self::individualBirthDate => true,
            self::corporateWebsite => true,
            self::legalAddress => true,
            self::counterpartyName => true,
            self::mainBrandName => true,
            self::counterpartyInn => true,
            self::counterpartyKpp => true,
            self::counterpartyOgrn => true,
            self::mainBankName => true,
            self::bankAccountNumber => true,
            self::bankCorrAccount => true,
            self::bankBic => true,
            self::counterpartyOkpo => true,
            self::counterpartyOkved => true,
            self::counterpartyPhone => true,
            self::counterpartyEmail => true,
            self::individualLastName => true,
            self::individualMiddleName => true,
            self::passportDetails => true,
            self::passportIssuer => true,
            self::registrationAddress => true,
            self::workPermitDetails => true,
            self::citizenshipDisplay => true,
            self::serviceBaseNorm => true,
            self::incomeWithTax => true,
            self::identityDocumentType => true,
            self::specialRequirements => true,
            self::personalSkills => true,
            self::signatoryShortName => true,
            self::individualNameLetters => true,
            self::individualMobilePhone => true,
            self::employedContractNumber => true,
            self::employedOfferName => true,
            self::employedOfferRevision => true,
            self::employedSigningBasis => true,
            self::employedFromDate => true,
            self::safetyInstructionCode => true,
            self::currentDate => true,
            self::specialistFullName => true,
            self::birthYear => true,
            self::primaryOfferName => true,
            self::additionalOfferName => true,
            self::instructionLocation => true,
            self::instructorName => true,
            self::taxReferenceLink => true,
            self::directorShortName => true,
            self::requestSequenceNumber => true,
            self::taxStatusName => true,
            self::serviceResult => true,
            self::serviceComposition => true,
            self::serviceQuality => true,
            self::serviceTimeliness => true,
            self::serviceRequirements => true,
            self::servicePrice => true,
            self::serviceStartDate => true,
            self::serviceEndDate => true,
            self::serviceExecutor => true,
        };
    }
}
