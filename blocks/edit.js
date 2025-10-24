/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, Notice } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
  const { columns, showDescription, showPlaceholder } = attributes;
  const [sites, setSites] = useState([]);
  const [isMultisite, setIsMultisite] = useState(true);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if this is a multisite and fetch sites
    apiFetch({ path: '/ms-featured-image/v1/sites' })
      .then((siteData) => {
        setSites(siteData);
        setLoading(false);
      })
      .catch(() => {
        // If sites endpoint fails, likely not multisite or no permission
        setIsMultisite(false);
        setLoading(false);
      });
  }, []);

  const blockProps = useBlockProps({
    className: `multisite-grid-columns-${columns}`,
  });

  if (loading) {
    return (
      <div {...blockProps}>
        <div className="multisite-grid-loading">
          {__('Loading multisite data...', 'ms-featured-image')}
        </div>
      </div>
    );
  }

  if (!isMultisite || sites.length === 0) {
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
            <ToggleControl
              label={__('Show Site Description', 'ms-featured-image')}
              checked={showDescription}
              onChange={(value) => setAttributes({ showDescription: value })}
            />
            <ToggleControl
              label={__('Show Placeholder for Missing Images', 'ms-featured-image')}
              checked={showPlaceholder}
              onChange={(value) => setAttributes({ showPlaceholder: value })}
            />
          </PanelBody>
        </InspectorControls>
        <div {...blockProps}>
          <Notice status="warning" isDismissible={false}>
            {!isMultisite
              ? __('This block only works on WordPress multisite networks.', 'ms-featured-image')
              : __('No sites found in the multisite network.', 'ms-featured-image')
            }
          </Notice>
        </div>
      </>
    );
  }

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
          <ToggleControl
            label={__('Show Site Description', 'ms-featured-image')}
            checked={showDescription}
            onChange={(value) => setAttributes({ showDescription: value })}
          />
          <ToggleControl
            label={__('Show Placeholder for Missing Images', 'ms-featured-image')}
            checked={showPlaceholder}
            onChange={(value) => setAttributes({ showPlaceholder: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="multisite-grid">
          {sites.map((site, index) => (
            <div key={site.blog_id || index} className="multisite-grid-item">
              <div className="multisite-grid-image">
                {showPlaceholder && (
                  <div className="multisite-grid-placeholder">
                    <span>{__('Site Image', 'ms-featured-image')}</span>
                  </div>
                )}
              </div>
              <div className="multisite-grid-content">
                <h3 className="multisite-grid-title">
                  {site.name || __('Site Name', 'ms-featured-image')}
                </h3>
                {showDescription && (
                  <p className="multisite-grid-description">
                    {site.description || __('Site description will appear here.', 'ms-featured-image')}
                  </p>
                )}
              </div>
            </div>
          ))}
        </div>
        <div className="multisite-grid-editor-note">
          {__('Preview: This block will display all sites in your multisite network with their featured images on the frontend.', 'ms-featured-image')}
        </div>
      </div>
    </>
  );
}
