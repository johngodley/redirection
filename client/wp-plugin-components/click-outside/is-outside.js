/**
 * Determine if an event is outside of a wrapper.
 *
 * @param {Event} ev - Click event
 * @param {HTMLElement|null} containerRef - DOM node for the wrapper
 * @listens click
 * @returns {boolean}}
 */
export default function isOutside( ev, containerRef ) {
	if ( ! containerRef ) {
		return false;
	}

	if ( containerRef.contains( ev.target ) ) {
		return false;
	}

	if ( ev.type === 'keydown' ) {
		return false;
	}

	return true;
}
