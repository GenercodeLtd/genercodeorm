<?php
namespace PressToJam;

use \GenerCodeOrm as Core;


class EntityFactory {

    static function create($slug) {
        switch($slug) {
            case 'users':
                return new Entity\Users();
                break;
            case 'audit':
                return new Entity\Audit();
                break;
            case 'queue':
                return new Entity\Queue();
                break;
            case 'company':
                return new Entity\Company();
                break;
            case 'events':
                return new Entity\Events();
                break;
            case 'course':
                return new Entity\Course();
                break;
            case 'booking':
                return new Entity\Booking();
                break;
            case 'counties':
                return new Entity\Counties();
                break;
            case 'additional-questions':
                return new Entity\AdditionalQuestions();
                break;
            case 'support-details':
                return new Entity\SupportDetails();
                break;
            case 'support-funding':
                return new Entity\SupportFunding();
                break;
            case 'agency-contracts':
                return new Entity\AgencyContracts();
                break;
            case 'trainer':
                return new Entity\Trainer();
                break;
            case 'grant-financials':
                return new Entity\GrantFinancials();
                break;
            case 'supports':
                return new Entity\Supports();
                break;
            case 'agencies':
                return new Entity\Agencies();
                break;
            case 'support-categories':
                return new Entity\SupportCategories();
                break;
            case 'support-match':
                return new Entity\SupportMatch();
                break;
            case 'primary-business':
                return new Entity\PrimaryBusiness();
                break;
            case 'allowable-expenses':
                return new Entity\AllowableExpenses();
                break;
            case 'support-application':
                return new Entity\SupportApplication();
                break;
            case 'jobs':
                return new Entity\Jobs();
                break;
            case 'agency-counties':
                return new Entity\AgencyCounties();
                break;
        }
    }
    
} 


