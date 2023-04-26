<?php

declare(strict_types=1);

namespace DTApi\Http\Controllers;

use App\Http\Traits\ResponseWithJsonTrait;
use DTApi\Models\Distance;
use DTApi\Models\Job;
use DTApi\Repository\BookingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    use ResponseWithJsonTrait;

    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function index(Request $request): JsonResponse
    {
        $has_any_admin_role = $request->__authenticatedUser->user_type === env('ADMIN_ROLE_ID')
            || $request->__authenticatedUser->user_type === env('SUPERADMIN_ROLE_ID');

        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);

        } else if ($has_any_admin_role) {
            $response = $this->repository->getAll($request);
        }

        return $this->successJsonResponse(null, $response);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return $this->successJsonResponse(null, $job);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);

        return $this->successJsonResponse(null, $response);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $job = $this->repository->find($id);
        if (! $job) {
            return $this->errorJsonResponse('Job Doesnt exist');
        }

        $data =  $request->except('_token', 'submit');
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, $data, $cuser);

        return $this->successJsonResponse("Job Successfully Updated!", $response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function immediateJobEmail(Request $request): JsonResponse
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $data = $this->repository->storeJobEmail($data);

        return $this->successJsonResponse(null, $data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getHistory(Request $request): JsonResponse
    {
        $result = [];
        if($user_id = $request->get('user_id')) {
            $result = $this->repository->getUsersJobsHistory($user_id, $request);
        }

        return $this->successJsonResponse(null, $result);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $result = $this->repository->acceptJob($data, $user);

        return $this->successJsonResponse(null, $result);
    }

    public function acceptJobWithId(Request $request): JsonResponse
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);

        return $this->successJsonResponse(null, $response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelJob(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $result = $this->repository->cancelJobAjax($data, $user);

        return $this->successJsonResponse("Job Cancelled", $result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function endJob(Request $request): JsonResponse
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);

        return $this->successJsonResponse("Job Ended", $response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerNotCall(Request $request): JsonResponse
    {
        $data = $request->all();
        $this->repository->customerNotCall($data);

        return $this->successJsonResponse();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPotentialJobs(Request $request): JsonResponse
    {
        $user = $request->__authenticatedUser;
        $result = $this->repository->getPotentialJobs($user);

        return $this->successJsonResponse(null, $result);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getRequestInputValue (Request $request, string $name)
    {
        return  $request->has($name) && ! empty($request->$name) ? $request->$name : '';
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function distanceFeed(Request $request): JsonResponse
    {
        $flagged = 'no';

        if ($request->boolean('flagged') && $request->filled('admincomment')) {
            $flagged = 'yes';
        } else {
            return $this->forbiddenJsonResponse("Please, add comment");
        }

        $jobid = $this->getRequestInputValue($request, 'jobid');
        $distance = $this->getRequestInputValue($request, 'distance');
        $time = $this->getRequestInputValue($request, 'time');

        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        $session = $this->getRequestInputValue($request, 'session_time');
        $manually_handled = $request->boolean('manually_handled') ? 'yes': 'no';
        $by_admin = $request->boolean('by_admin') ? 'yes': 'no';
        $admincomment = $this->getRequestInputValue($request, 'admincomment');

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            $data = array(
                'admin_comments' => $admincomment,
                'flagged' => $flagged, 'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            );

            Job::where('id', '=', $jobid)->update($data);
        }

        return $this->successJsonResponse('Record Updated');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function reopen(Request $request): JsonResponse
    {
        $data = $request->all();
        $is_reopened = $this->repository->reopen($data);

        if (! $is_reopened) {
            return $this->errorJsonResponse("Please try again");
        }

        return $this->successJsonResponse('Tolk cancelled');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resendNotifications(Request $request): JsonResponse
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return $this->successJsonResponse('Push sent');
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return JsonResponse
     */
    public function resendSMSNotifications(Request $request): JsonResponse
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return $this->successJsonResponse('SMS sent');
        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage());
        }
    }
}
