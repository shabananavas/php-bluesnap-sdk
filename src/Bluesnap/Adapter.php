<?php

namespace Bluesnap;

use Bluesnap\Exceptions\BluesnapException;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Adapter
 */
class Adapter
{
    public static function get($model, $id = null, $options = [])
    {
        $query_params = Utility::getOption($options, 'query_params', null);
        $target_parameter = Utility::getOption($options, 'target_parameter', null);
        $endpoint_extension = Utility::getOption($options, 'endpoint_extension', null);

        try
        {
            $is_collection = $id === null;
            $endpoint = Utility::getModelEndpoint($model, $id);
            if ($endpoint_extension) {
                $endpoint .= "/{$endpoint_extension}";
            }

            $response = Api::get($endpoint, $id, $query_params);
            $response = Utility::setupModel($model, $response, $is_collection, $target_parameter);

            return new Response('success', $response);
        }

        catch (ClientException $e)
        {
            if ($e->getCode() === 401)
            {
                return new Response('error', 'Invalid BlueSnap Credentials');
            }

            $message = $e->getResponse()->getBody()->getContents();
            $message = Utility::parseBluesnapErrors($message);

            return new Response('error', $message);
        }

        catch (BluesnapException $e)
        {
            return new Response('error', $e->getMessage());
        }
    }

    /**
     * @param $model
     * @param $data
     * @param array $options
     * @return Response
     */
    public static function create($model, $data, $options = [])
    {
        $id_in_header = Utility::getOption($options, 'id_in_header', true);

        try
        {
            $data = Utility::objectToArray($data);

            if (empty($data['subscription_id'])) {
              $data['subscription_id'] = NULL;
            }
            $endpoint = Utility::getModelEndpoint($model, NULL, $data['subscription_id']);

            $response = Api::post($endpoint, $data, $id_in_header);

            if ($id_in_header)
            {
                $data = $response + $data;  //  combine arrays
                $model = Utility::setupModel($model, $data);
            }
            else
            {
                $model = Utility::setupModel($model, $response, false);
            }

            return new Response('success', $model);
        }

        catch (ClientException $e)
        {
            if ($e->getCode() === 401)
            {
                return new Response('error', 'Invalid BlueSnap Credentials');
            }

            $message = $e->getResponse()->getBody()->getContents();
            $message = Utility::parseBluesnapErrors($message);

            return new Response('error', $message);
        }
    }

    public static function update($model, $id, $data, $options = [])
    {
        $query_params = Utility::getOption($options, 'query_params', null);
        $id_in_url = Utility::getOption($options, 'id_in_url', true);

        try
        {
            $data = Utility::objectToArray($data);
            $endpoint = Utility::getModelEndpoint($model, $id);
            $endpoint = $id_in_url ? $endpoint .'/'. $id : $endpoint;

            $response = Api::put($endpoint, $data, $query_params);
            $model = Utility::setupModel($model, $response);

            return new Response('success', $model);
        }

        catch (ClientException $e)
        {
            if ($e->getCode() === 401)
            {
                return new Response('error', 'Invalid BlueSnap Credentials');
            }

            $message = $e->getResponse()->getBody()->getContents();
            $message = Utility::parseBluesnapErrors($message);

            return new Response('error', $message);
        }

        catch (BluesnapException $e)
        {
            return new Response('error', $e->getMessage());
        }
    }

    /**
     * @param $model
     * @param null $id
     * @return Response
     */
    public static function delete($model, $id)
    {
        try
        {
            $endpoint = Utility::getModelEndpoint($model, $id);
            $endpoint = $endpoint .'/'. $id;
            Api::delete($endpoint);

            return new Response('success', 'Record '. $id .' deleted.');
        }

        catch (ClientException $e)
        {
            if ($e->getCode() === 401)
            {
                return new Response('error', 'Invalid BlueSnap Credentials');
            }

            $message = $e->getResponse()->getBody()->getContents();
            $message = Utility::parseBluesnapErrors($message);

            return new Response('error', $message);
        }

        catch (BluesnapException $e)
        {
            return new Response('error', $e->getMessage());
        }
    }
}
