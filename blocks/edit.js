/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n'

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { PanelBody, RangeControl, TextControl, ToggleControl, Notice } from '@wordpress/components'
import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss'

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {JSX.Element} Element to render.
 */
export default function Edit ({ attributes, setAttributes }) {
  const { columns, size, showPlaceholder } = attributes
  const [sites, setSites] = useState([])
  const [isMultisite, setIsMultisite] = useState(true)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check if this is a multisite and fetch sites
    apiFetch({ path: '/ms-featured-image/v1/sites' })
      .then((siteData) => {
        setSites(siteData)
        setLoading(false)
      })
      .catch(() => {
        // If sites endpoint fails, likely not multisite or no permission
        setIsMultisite(false)
        setLoading(false)
      })
  }, [])

  const blockProps = useBlockProps({
    className: `multisite-grid-columns-${columns}`,
  })

  if (loading) {
    return (
      <div {...blockProps}>
        <div className="multisite-grid-loading">
          {__('Loading multisite data...', 'ms-featured-image')}
        </div>
      </div>
    )
  }

  if (!isMultisite || sites.length === 0) {
    return (
      <div {...blockProps}>
        <Notice status="warning" isDismissible={false}>
          {!isMultisite
            ? __('This block only works on WordPress multisite networks.', 'ms-featured-image')
            : __('No sites found in the multisite network.', 'ms-featured-image')
          }
        </Notice>
      </div>
    )
  }

  console.log(sites)

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Grid Settings', 'ms-featured-image')}>
          <RangeControl
            label={__('Columns', 'ms-featured-image')}
            value={columns}
            onChange={(value) => setAttributes({ columns: value })}
            min={1}
            max={6}
          />
          <TextControl
            label={__('Image size', 'ms-featured-image')}
            value={size}
            onChange={(value) => setAttributes({ size: value })}
            help={__('Select the image size', 'ms-featured-image')}
          />
          <ToggleControl
            label={__('Show Placeholder for Missing Images', 'ms-featured-image')}
            checked={showPlaceholder}
            onChange={(value) => setAttributes({ showPlaceholder: value })}
          />
        </PanelBody>
      </InspectorControls>

      <section {...blockProps}>
        <header>
          <h2>{__('Sites in the network', 'ms-featured-image')}</h2>
        </header>
        <article>
          <div className="row">
            {sites.map((site, index) => (

              <div key={site.blog_id || index}>
                <figure>
                  <figcaption>
                    {site.name}
                  </figcaption>
                  {showPlaceholder && (
                    <img src="https://placeholdit.com/600x400/dddddd/999999"/>
                  )}
                </figure>
              </div>
            ))}
          </div>
        </article>
      </section>
    </>
  )
}
