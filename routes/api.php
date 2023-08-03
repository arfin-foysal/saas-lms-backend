<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MasterSettingsController;
use App\Http\Controllers\PromotionalNoticeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PromotionalSiteController;
use App\Http\Controllers\WebsiteController;

Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::get('country-list', [MasterSettingsController::class, 'countryList']);
Route::get('school-list', [SchoolController::class, 'schoolList']);
Route::get('get-expert-list', [AuthController::class, 'getExpertList']);

Route::post('client-info-save', [PromotionalSiteController::class, 'clientInfoSave']);
Route::get('client-list', [PromotionalSiteController::class, 'clientList']);

// Location
Route::get('division-list', [LocationController::class, 'divisionList']);
Route::get('district-list/{division_id}', [LocationController::class, 'districtListByID']);
Route::get('upazila-list/{district_id}', [LocationController::class, 'upazilaListByID']);
Route::get('area-list/{upazilla_id}', [LocationController::class, 'unionListByID']);

Route::get('menu-list', [MasterSettingsController::class, 'adminMenuList']);

//Tags
Route::get('tag-list', [MasterSettingsController::class, 'tagsList']);

Route::get('organization-list', [OrganizationController::class, 'organizationList']);
Route::get('settings-by-slug/{slug}', [MasterSettingsController::class, 'settingDetailsByID']);

Route::get('class-list', [ContentController::class, 'classList']);

Route::group(['prefix' => 'mobile'], function () {
    Route::get('menu-list', [MasterSettingsController::class, 'mobileMenuList']);
    Route::get('course-list-by-id/{menu_id}', [MasterSettingsController::class, 'courseListByID']);
    Route::get('all-course-list', [CourseController::class, 'allCourseList']);
    Route::get('all-content-list', [ContentController::class, 'allContentList']);
    Route::get('course-details-by-id/{course_id}', [CourseController::class, 'courseDetailsByID']);
    Route::get('all-mentor-list', [MentorController::class, 'allMentorList']);
    Route::get('mentor-details-by-id/{mentor_id}', [MentorController::class, 'mentorDetailsByID']);
});

Route::group(['prefix' => 'website'], function () {
    Route::get('menu-list', [MasterSettingsController::class, 'websiteMenuList']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('get-profile', [AuthController::class, 'getProfile']);
    Route::post('profile-update', [AuthController::class, 'updateUser']);
    Route::post('update-interest', [AuthController::class, 'updateInterest']);

    //Menu Settings
    Route::get('admin/menu-list', [MasterSettingsController::class, 'adminMenuList']);
    Route::post('admin/menu-save-or-update', [MasterSettingsController::class, 'saveOrUpdateMenu']);

    //Organization
    Route::post('admin/organization-save-or-update', [OrganizationController::class, 'saveOrUpdateOrganization']);
    Route::post('admin/settings-update', [OrganizationController::class, 'updateSettings']);

    //Tags
    Route::post('admin/tag-save-or-update', [MasterSettingsController::class, 'saveOrUpdateTags']);

    //USER ROUTES
    Route::post('update-tags', [StudentController::class, 'updateInterests']);


    // common routes

    Route::get('admin/subject-by-class-id/{class_id}', [ContentController::class, 'subjectListByClassID']);
    Route::get('admin/chapter-by-subject-id/{subject_id}', [ContentController::class, 'chapterListBySubjectID']);
    Route::get('admin/script-list-by-chapter-id/{chapter_id}', [ContentController::class, 'scriptListByChapterID']);
    Route::get('admin/video-list-by-chapter-id/{chapter_id}', [ContentController::class, 'videoListByChapterID']);
    Route::get('admin/quiz-list-by-chapter-id/{chapter_id}', [ContentController::class, 'quizListByChapterID']);


    //admin Content Routes 
    Route::get('admin/class-list', [ContentController::class, 'classList']);
    Route::post('admin/class-save-or-update', [ContentController::class, 'saveOrUpdateClass']);
    Route::get('admin/subject-list', [ContentController::class, 'subjectList']);
    Route::post('admin/subject-save-or-update', [ContentController::class, 'saveOrUpdateSubject']);



    Route::get('admin/chapter-list', [ContentController::class, 'chapterList']);
    Route::post('admin/chapter-save-or-update', [ContentController::class, 'saveOrUpdateChapter']);
    Route::get('admin/video-chapter-list', [ContentController::class, 'videoChapterList']);
    Route::post('admin/chapter-video-save-or-update', [ContentController::class, 'saveOrUpdateChapterVideo']);

    Route::get('admin/chapter-script-list', [ContentController::class, 'scriptChapterList']);
    Route::post('admin/chapter-script-save-or-update', [ContentController::class, 'saveOrUpdateScript']);

    Route::post('admin/chapter-quiz-save-or-update', [ContentController::class, 'saveOrUpdateQuiz']);
    Route::get('admin/chapter-quiz-list', [ContentController::class, 'chapterQuizList']);

    Route::get('admin/question-set-list', [ContentController::class, 'questionSetList']);
    Route::get('admin/question-list-by-quiz/{id}', [ContentController::class, 'quizQuestionList']);
    Route::post('admin/chapter-quiz-question-save-or-update', [ContentController::class, 'saveOrUpdateQuizQuestion']);
    Route::post('admin/excel-question-upload', [ContentController::class, 'excelQuestionUpload']);
    Route::delete('admin/delete-question/{id}', [ContentController::class, 'deleteQuestion']);

    //admin Website 
    Route::post('admin/website-page-save-or-update', [MasterSettingsController::class, 'websitePageSaveOrUpdate']);
    Route::get('admin/website-page-list/{id}', [MasterSettingsController::class, 'websitePageList']);

    //admin Course 

    Route::get('admin/course-list', [CourseController::class, 'courseList']);
    Route::post('admin/course-save-or-update', [CourseController::class, 'saveOrUpdateCourse']);

    Route::post('admin/course-outline-save-or-update', [CourseController::class, 'saveOrUpdateCourseOutline']);
    Route::get('admin/course-outline-list/{id}', [CourseController::class, 'courseOutlineList']);

    Route::delete('admin/delete-course-outline/{id}', [CourseController::class, 'courseOutlineDelete']);
    Route::get('admin/content-list', [ContentController::class, 'contentList']);
    Route::post('admin/content-save-or-update', [ContentController::class, 'saveOrUpdateContent']);
    Route::post('admin/content-outline-save-or-update', [ContentController::class, 'saveOrUpdateContentOutline']);
    Route::get('admin/content-outline-list/{id}', [ContentController::class, 'contentOutlineList']);

    Route::delete('admin/delete-course-outline/{id}', [ContentController::class, 'courseOutlineDelete']);
    Route::delete('admin/delete-content-outline/{id}', [ContentController::class, 'contentOutlineDelete']);


    Route::post('admin/faq-save-or-update', [CourseController::class, 'saveOrUpdateFaq']);
    Route::get('admin/faq-list/{id}', [CourseController::class, 'faqList']);
    Route::delete('admin/delete-faq/{id}', [CourseController::class, 'faqDelete']);






    //Old Application
    /* //Master Settings
    Route::get('syllabus-list', [MasterSettingsController::class, 'packageTypeList']);
    Route::get('grade-list', [MasterSettingsController::class, 'gradeList']);
    Route::get('category-list', [MasterSettingsController::class, 'categoryList']);

    //Package 
    Route::get('package-list', [PackageController::class, 'packageList']);
    Route::get('package-details-by-id/{package_id}', [PackageController::class, 'packageDetailsByID']);

    //Topic
    Route::get('all-topic-list', [TopicController::class, 'allTopicList']);
    Route::post('filter-topic-list', [TopicController::class, 'fillterTopicList']);
    Route::get('filter-topic-list/{syllabus_id}', [TopicController::class, 'fillterTopicListByTypeID']);

    //Package Details (For User)
    Route::get('my-package-list', [ConsumeController::class, 'myPackageList']);
    Route::get('my-active-syllebus-list/{payment_id}', [ConsumeController::class, 'myActiveSyllebusList']);
    
    //Promotional Notice
    Route::get('promotional-news-list', [PromotionalNoticeController::class, 'promotionalNoticeList']);

    //Admin
    Route::get('admin/syllabus-list', [MasterSettingsController::class, 'admin_PackageTypeList']);
    Route::post('admin/syllabus-save-or-update', [MasterSettingsController::class, 'saveOrUpdatePackageType']);
    Route::get('admin/grade-list', [MasterSettingsController::class, 'adminGradeList']);
    Route::post('admin/grade-save-or-update', [MasterSettingsController::class, 'saveOrUpdateGrade']);
    Route::get('admin/category-list', [MasterSettingsController::class, 'adminCategoryList']);
    Route::post('admin/category-save-or-update', [MasterSettingsController::class, 'saveOrUpdateCategory']);

    Route::get('admin/package-list', [PackageController::class, 'adminPackageList']);
    Route::post('admin/package-save-or-update', [PackageController::class, 'saveOrUpdatePackage']);
    Route::get('admin/benefit-list-by-id/{package_id}', [PackageController::class, 'adminBenefitListByID']);
    Route::post('admin/benefit-save-or-update', [PackageController::class, 'saveOrUpdateBenefit']);
    Route::post('admin/benefit-delete', [PackageController::class, 'adminDeleteBenefitByID']);

    Route::get('admin/news-list', [PromotionalNoticeController::class, 'adminPromotionalNoticeList']);
    Route::post('admin/news-save-or-update', [PromotionalNoticeController::class, 'saveOrUpdatePromotionalNotice']);

    Route::get('admin/topic-list', [TopicController::class, 'adminTopicList']);
    Route::post('admin/topic-save-or-update', [TopicController::class, 'saveOrUpdateTopic']);

    Route::get('admin/school-list', [SchoolController::class, 'adminSchoolList']);
    Route::post('admin/school-save-or-update', [SchoolController::class, 'saveOrUpdateSchool']);
    Route::get('admin/expert-list', [AuthController::class, 'getAdminExpertList']);
    Route::post('admin/save-update-expert', [AuthController::class, 'saveOrUpdateUser']);
    Route::get('admin/payment-list', [PaymentController::class, 'adminPaymentList']);
    Route::post('delete-account', [AuthController::class, 'deleteUserAccount']);
    
    //Payment 
    Route::post('mobile/make-payment', [PaymentController::class, 'makePaymentMobile']);
    Route::get('payment-list', [PaymentController::class, 'myPaymentList']);
    Route::get('package-details-by-payment-id/{payment_id}', [PaymentController::class, 'packageDetailsByPaymentID']);

    //Payment Web
    Route::post('web/make-payment', [PaymentController::class, 'makePaymentWeb']);

    //Submit Correction 
    Route::post('check-availability', [CorrectionController::class, 'checkAvailable']);
    Route::post('submit-correction', [CorrectionController::class, 'submitCorrection']);
    Route::post('edit-correction-by-student', [CorrectionController::class, 'editCorrectionByStudent']);
    Route::post('update-is-seen-by-student', [CorrectionController::class, 'updateIsSeenByStudent']);
    Route::get('pending-correction-count', [CorrectionController::class, 'getPendingCorrectionCount']);
    Route::get('correction-list', [CorrectionController::class, 'getCorrectionList']);
    Route::get('correction-details-by-id/{correction_id}', [CorrectionController::class, 'getCorrectionDetailsByID']);
    Route::get('expert-correction-list', [CorrectionController::class, 'getExpertCorrectionList']);
    Route::post('accept-correction', [CorrectionController::class, 'acceptPendingCorrection']);
    Route::post('submit-feedback', [CorrectionController::class, 'submitFeedback']);
    Route::get('mark-grade-list', [MasterSettingsController::class, 'markGradeList']);
    Route::post('update-feedback', [CorrectionController::class, 'editFeedback']);
    Route::post('student-resubmission', [CorrectionController::class, 'studentResubmission']);
    Route::post('submit-final-note', [CorrectionController::class, 'submitExpertFinalNote']);
    Route::post('submit-rating-by-student', [CorrectionController::class, 'submitStudentRating']);
    Route::get('expert-dashboard', [CorrectionController::class, 'getMiniDashboardInfo']);

    Route::get('my-balance-list', [ConsumeController::class, 'myBalanceList']); */
});

Route::group(['prefix' => 'open'], function () {
    // Package 
    Route::get('package-list', [PackageController::class, 'packageList']);
    Route::get('package-details-by-id/{package_id}', [PackageController::class, 'packageDetailsByID']);
    Route::get('syllabus-list', [MasterSettingsController::class, 'packageTypeList']);
});

Route::post('trancate-data', [MasterSettingsController::class, 'trancateData']);

Route::any('{url}', function () {
    return response()->json([
        'status' => false,
        'message' => 'Route Not Found!',
        'data' => []
    ], 404);
})->where('url', '.*');
