<?php namespace Fisdap\Data\Repository;

use Fisdap\Api\ServiceAccounts\Entities\ServiceAccount;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Fisdap\Api\ServiceAccounts\Repository\ServiceAccountsRepository;
use Fisdap\Data\AirwayManagement\AirwayManagementRepository;
use Fisdap\Data\Base\BaseLegacyRepository;
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\ClassSection\ClassSectionLegacyRepository;
use Fisdap\Data\Discount\DiscountLegacyRepository;
use Fisdap\Data\Ethnicity\EthnicityRepository;
use Fisdap\Data\Event\EventLegacyRepository;
use Fisdap\Data\Gender\GenderRepository;
use Fisdap\Data\Goal\GoalRepository;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\MentalAlertness\MentalAlertnessRepository;
use Fisdap\Data\MentalOrientation\MentalOrientationRepository;
use Fisdap\Data\Narrative\NarrativeSectionDefinitionRepository;
use Fisdap\Data\Order\Configuration\OrderConfigurationRepository;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Order\Permission\OrderPermissionRepository;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Permission\PermissionHistoryLegacyRepository;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\Data\Portfolio\PortfolioUploadsRepository;
use Fisdap\Data\Practice\PracticeCategoryRepository;
use Fisdap\Data\Practice\PracticeDefinitionRepository;
use Fisdap\Data\Practice\PracticeItemRepository;
use Fisdap\Data\Preceptor\PreceptorLegacyRepository;
use Fisdap\Data\PreceptorRating\PreceptorRatingRepository;
use Fisdap\Data\Product\Package\ProductPackageRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\Profession\ProfessionRepository;
use Fisdap\Data\Program\Procedures\ProgramCardiacProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramIvProcedureRepository;
use Fisdap\Data\Program\Procedures\ProgramLabAssessmentRepository;
use Fisdap\Data\Program\Procedures\ProgramMedTypeRepository;
use Fisdap\Data\Program\Procedures\ProgramOtherProcedureRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Program\RequiredShiftEvaluations\ProgramRequiredShiftEvaluationsRepository;
use Fisdap\Data\Program\Procedures\ProgramAirwayProcedureRepository;
use Fisdap\Data\Program\Settings\ProgramSettingsRepository;
use Fisdap\Data\Program\Type\ProgramTypeLegacyRepository;
use Fisdap\Data\ProgramPreceptor\ProgramPreceptorLegacyRepository;
use Fisdap\Data\Report\DoctrineReportRepository;
use Fisdap\Data\Report\ReportRepository;
use Fisdap\Data\Requirement\RequirementAutoAttachmentRepository;
use Fisdap\Data\Requirement\RequirementRepository;
use Fisdap\Data\Role\RoleRepository;
use Fisdap\Data\Run\RunRepository;
use Fisdap\Data\Scenario\ScenarioRepository;
use Fisdap\Data\ScheduleEmail\ScheduleEmailRepository;
use Fisdap\Data\SerialNumber\SerialNumberLegacyRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\Shift\ShiftRequestRepository;
use Fisdap\Data\ShiftAttendance\ShiftAttendanceRepository;
use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Data\Skill\AirwayRepository;
use Fisdap\Data\Skill\CardiacInterventionRepository;
use Fisdap\Data\Skill\IvRepository;
use Fisdap\Data\Skill\MedRepository;
use Fisdap\Data\Skill\OtherInterventionRepository;
use Fisdap\Data\Skill\VitalRepository;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Data\Timezone\TimezoneRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Data\Verification\VerificationRepository;
use Fisdap\Entity\Airway;
use Fisdap\Entity\AirwayManagement;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\CardiacIntervention;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\ClassSectionLegacy;
use Fisdap\Entity\DiscountLegacy;
use Fisdap\Entity\Ethnicity;
use Fisdap\Entity\EventLegacy;
use Fisdap\Entity\Gender;
use Fisdap\Entity\Goal;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\MentalAlertness;
use Fisdap\Entity\MentalOrientation;
use Fisdap\Entity\Iv;
use Fisdap\Entity\Med;
use Fisdap\Entity\NarrativeSectionDefinition;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\OrderPermission;
use Fisdap\Entity\OtherIntervention;
use Fisdap\Entity\Patient;
use Fisdap\Entity\Permission;
use Fisdap\Entity\PermissionHistoryLegacy;
use Fisdap\Entity\PortfolioUploads;
use Fisdap\Entity\PracticeCategory;
use Fisdap\Entity\PracticeDefinition;
use Fisdap\Entity\PracticeItem;
use Fisdap\Entity\PreceptorLegacy;
use Fisdap\Entity\PreceptorRating;
use Fisdap\Entity\Product;
use Fisdap\Entity\ProductPackage;
use Fisdap\Entity\Profession;
use Fisdap\Entity\ProgramAirwayProcedure;
use Fisdap\Entity\ProgramCardiacProcedure;
use Fisdap\Entity\ProgramIvProcedure;
use Fisdap\Entity\ProgramLabAssessment;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\ProgramPreceptorLegacy;
use Fisdap\Entity\ProgramRequiredShiftEvaluations;
use Fisdap\Entity\ProgramMedType;
use Fisdap\Entity\ProgramOtherProcedure;
use Fisdap\Entity\ProgramSettings;
use Fisdap\Entity\ProgramTypeLegacy;
use Fisdap\Entity\Report;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\RequirementAutoAttachment;
use Fisdap\Entity\Role;
use Fisdap\Entity\Run;
use Fisdap\Entity\Scenario;
use Fisdap\Entity\ScheduleEmail;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\ShiftAttendence;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\ShiftRequest;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Entity\SlotAssignment;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\Timezone;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Fisdap\Entity\Verification;
use Fisdap\Entity\Vital;
use Illuminate\Support\ServiceProvider;
use Zend_Registry;

/**
 * Class EntityRepositoryServiceProvider
 *
 * Catch-all service provider for Doctrine repositories
 *
 * @package Fisdap\Data\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class EntityRepositoryServiceProvider extends ServiceProvider
{
    protected static $entityRepositoryInterfaceMap = [
        Airway::class                               => AirwayRepository::class,
        AirwayManagement::class                     => AirwayManagementRepository::class,
        BaseLegacy::class                           => BaseLegacyRepository::class,
        CardiacIntervention::class                  => CardiacInterventionRepository::class,
        CertificationLevel::class                   => CertificationLevelRepository::class,
        ClassSectionLegacy::class                   => ClassSectionLegacyRepository::class,
        DiscountLegacy::class                       => DiscountLegacyRepository::class,
        Ethnicity::class                            => EthnicityRepository::class,
        EventLegacy::class                          => EventLegacyRepository::class,
        Gender::class                               => GenderRepository::class,
        Goal::class                                 => GoalRepository::class,
        InstructorLegacy::class                     => InstructorLegacyRepository::class,
        Iv::class                                   => IvRepository::class,
        Med::class                                  => MedRepository::class,
        MentalAlertness::class                      => MentalAlertnessRepository::class,
        MentalOrientation::class                    => MentalOrientationRepository::class,
        NarrativeSectionDefinition::class           => NarrativeSectionDefinitionRepository::class,
        Order::class                                => OrderRepository::class,
        OrderConfiguration::class                   => OrderConfigurationRepository::class,
        OrderPermission::class                      => OrderPermissionRepository::class,
        OtherIntervention::class                    => OtherInterventionRepository::class,
        Patient::class                              => PatientRepository::class,
        Permission::class                           => PermissionRepository::class,
        PermissionHistoryLegacy::class              => PermissionHistoryLegacyRepository::class,
        PortfolioUploads::class                     => PortfolioUploadsRepository::class,
        PracticeCategory::class                     => PracticeCategoryRepository::class,
        PracticeDefinition::class                   => PracticeDefinitionRepository::class,
        PreceptorLegacy::class                      => PreceptorLegacyRepository::class,
        PreceptorRating::class                      => PreceptorRatingRepository::class,
        Product::class                              => ProductRepository::class,
        ProductPackage::class                       => ProductPackageRepository::class,
        Profession::class                           => ProfessionRepository::class,
        ProgramLegacy::class                        => ProgramLegacyRepository::class,
        ProgramAirwayProcedure::class               => ProgramAirwayProcedureRepository::class,
        ProgramCardiacProcedure::class              => ProgramCardiacProcedureRepository::class,
        ProgramIvProcedure::class                   => ProgramIvProcedureRepository::class,
        ProgramLabAssessment::class                 => ProgramLabAssessmentRepository::class,
        ProgramMedType::class                       => ProgramMedTypeRepository::class,
        ProgramOtherProcedure::class                => ProgramOtherProcedureRepository::class,
        ProgramPreceptorLegacy::class               => ProgramPreceptorLegacyRepository::class,
        ProgramTypeLegacy::class                    => ProgramTypeLegacyRepository::class,
        ProgramRequiredShiftEvaluations::class      => ProgramRequiredShiftEvaluationsRepository::class,
        ProgramSettings::class                      => ProgramSettingsRepository::class,
        Report::class                               => ReportRepository::class,
        Requirement::class                          => RequirementRepository::class,
        RequirementAutoAttachment::class            => RequirementAutoAttachmentRepository::class,
        Role::class                                 => RoleRepository::class,
        Run::class                                  => RunRepository::class,
        ScheduleEmail::class                        => ScheduleEmailRepository::class,
        Scenario::class                             => ScenarioRepository::class,
        ServiceAccount::class                       => ServiceAccountsRepository::class,
        ServiceAccountPermission::class             => ServiceAccountPermissionsRepository::class,
        ShiftAttendence::class                      => ShiftAttendanceRepository::class,
        ShiftLegacy::class                          => ShiftLegacyRepository::class,
        ShiftRequest::class                         => ShiftRequestRepository::class,
        SerialNumberLegacy::class                   => SerialNumberLegacyRepository::class,
        SiteLegacy::class                           => SiteLegacyRepository::class,
        SlotAssignment::class                       => SlotAssignmentRepository::class,
        StudentLegacy::class                        => StudentLegacyRepository::class,
        Timezone::class                             => TimezoneRepository::class,
        PracticeItem::class                         => PracticeItemRepository::class,
        User::class                                 => UserRepository::class,
        UserContext::class                          => UserContextRepository::class,
        Vital::class                                => VitalRepository::class,
        Verification::class                         => VerificationRepository::class,
    ];


    public function register()
    {
        foreach (self::$entityRepositoryInterfaceMap as $entityName => $repoClassName) {
            $this->app->singleton(
                $repoClassName,
                function () use ($entityName) {
                    /** @var DoctrineRepository $repo */
                    $repo = Zend_Registry::get('doctrine')->getEntityManager()->getRepository($entityName);
                    $repo->setLogger(Zend_Registry::get('logger'));

                    return $repo;
                }
            );
        }
    }
}
