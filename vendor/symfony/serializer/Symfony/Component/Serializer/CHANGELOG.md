CHANGELOG
=========

2.5.0
-----

 * added support for `is.*` getters in `GetSetMethodNormalizer`

2.4.0
-----

 * added `$context` support for XMLEncoder.
 * [DEPRECATION] JsonEncode and JsonDecode where modified to throw
   an exception if error found. No need for get*Error() functions

2.3.0
-----

 * added `GetSetMethodNormalizer::setCamelizedAttributes` to allow calling
   camel cased methods for underscored properties

2.2.0
-----

 * [BC BREAK] All Serializer, Normalizer and Encoder interfaces have been
   modified to include an optional `$context` array parameter.
 * The XML Root name can now be configured with the `xml_root_name`
   parameter in the context option to the `XmlEncoder`.
 * Options to `json_encode` and `json_decode` can be passed through
   the context options of `JsonEncode` and `JsonDecode` encoder/decoders.

2.1.0
-----

 * added DecoderInterface::supportsDecoding(),
   EncoderInterface::supportsEncoding()
 * removed NormalizableInterface::denormalize(),
   NormalizerInterface::denormalize(),
   NormalizerInterface::supportsDenormalization()
 * removed normalize() denormalize() encode() decode() supportsSerialization()
   supportsDeserialization() supportsEncoding() supportsDecoding()
   getEncoder() from SerializerInterface
 * Serializer now implements NormalizerInterface, DenormalizerInterface,
   EncoderInterface, DecoderInterface in addition to SerializerInterface
 * added DenormalizableInterface and DenormalizerInterface
 * [BC BREAK] changed `GetSetMethodNormalizer`'s key names from all lowercased
   to camelCased (e.g. `mypropertyvalue` to `myPropertyValue`)
 * [BC BREAK] convert the `item` XML tag to an array

    ``` xml
    <?xml version="1.0"?>
    <response>
        <item><title><![CDATA[title1]]></title></item><item><title><![CDATA[title2]]></title></item>
    </response>
    ```

    Before:

        Array()

    After:

        Array(
            [item] => Array(
                [0] => Array(
                    [title] => title1
                )
                [1] => Array(
                    [title] => title2
                )
            )
        )
