/**
 * @typedef MultiOptionValue
 * @type
 * @property {string} label - User viewable label for the option
 * @property {string} value - Value string for the option
 */

/**
 * @typedef MultiOptionGroupValue
 * @type
 * @property {string} label - User viewable label for the option
 * @property {string} value - Value string for the option
 * @property {MultiOptionValue[]} [options] - Array of MultiOptions
 * @property {boolean} [multiple=false] - Whether multiple values are allowed
 */

/**
 * @callback CustomBadge
 * @param {string[]} badges - The badges
 * @returns {string[]} Modified badges
 */
