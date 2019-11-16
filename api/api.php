<?php

/**
 * @apiDefine 401Error
 *
 * @apiError (Error 401) rest_forbidden You are not authorized to access this API endpoint
 * @apiErrorExample {json} 401 Error Response:
 *     HTTP/1.1 401 Bad Request
 *     {
 *       "code": "rest_forbidden",
 *       "message": "Sorry, you are not allowed to do that."
 *     }
 */

/**
 * @apiDefine 404Error
 *
 * @apiError (Error 404) rest_no_route Endpoint not found
 * @apiErrorExample {json} 404 Error Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "code": "rest_no_route",
 *       "message": "No route was found matching the URL and request method"
 *     }
 */

/**
 * @apiDefine 400Error
 *
 * @apiError rest_forbidden You are not authorized to access this API endpoint
 * @apiErrorExample {json} 400 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "error": "invalid",
 *       "message": "Invalid request"
 *     }
 */

/**
 * @apiDefine 400MissingError
 * @apiError (Error 400) rest_missing_callback_param Some required parameters are not present or not in the correct format
 * @apiErrorExample {json} 400 Error Response:
 *     HTTP/1.1 400 Bad Request
 *     {
 *       "code": "rest_missing_callback_param",
 *       "message": "Missing parameter(s): PARAM"
 *     }
 */
