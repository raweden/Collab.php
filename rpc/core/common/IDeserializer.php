<?php

/**
 * interface for deserializers.
 * 
 * @author Ariel Sommeria-klein
 */
interface IDeserializer {
    
    /**
     * Deserialize the data.
     * 
     * @param array $getData typically the $_GET array. 
     * @param array $postData typically the $_POST array.
     * @param String $rawPostData
     * 
     * @return mixed the deserialized data. For example an Amf packet.
     */
    public function deserialize(array $getData, array $postData, $rawPostData);
}
?>
