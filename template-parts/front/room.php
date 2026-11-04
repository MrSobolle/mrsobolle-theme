<?php
/**
 * Very subdued deep room. Real perspective lines with no raster background.
 * The full layer is decorative; cursor parallax is handled separately.
 *
 * @package MrSobolle
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="studio-room" data-studio-room aria-hidden="true">
    <svg class="studio-room__geometry" viewBox="0 0 1600 900" preserveAspectRatio="xMidYMid slice" focusable="false">
        <defs>
            <linearGradient id="studio-wall-left" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#1c2e55" stop-opacity=".17"/>
                <stop offset=".95" stop-color="#070b17" stop-opacity=".12"/>
            </linearGradient>
            <linearGradient id="studio-wall-right" x1="1" y1="0" x2="0" y2="0">
                <stop offset="0" stop-color="#261a49" stop-opacity=".17"/>
                <stop offset=".95" stop-color="#080b17" stop-opacity=".12"/>
            </linearGradient>
            <linearGradient id="studio-floor" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#121e39" stop-opacity=".18"/>
                <stop offset="1" stop-color="#12182e" stop-opacity=".02"/>
            </linearGradient>
            <radialGradient id="studio-vanishing-light">
                <stop offset="0" stop-color="#6b82e6" stop-opacity=".09"/>
                <stop offset="1" stop-color="#101525" stop-opacity="0"/>
            </radialGradient>
            <!-- Projection masks match the existing perspective planes. -->
            <clipPath id="studio-room-left-clip">
                <path d="M0 0L690 310V530L0 900Z"/>
            </clipPath>
            <clipPath id="studio-room-right-clip">
                <path d="M1600 0L910 310V530L1600 900Z"/>
            </clipPath>
            <clipPath id="studio-room-floor-clip">
                <path d="M0 900L690 530H910L1600 900Z"/>
            </clipPath>
            <radialGradient id="studio-room-reflection-blue">
                <stop offset="0" stop-color="#9dd5ff" stop-opacity=".44"/>
                <stop offset=".38" stop-color="#578aca" stop-opacity=".23"/>
                <stop offset="1" stop-color="#24457c" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="studio-room-reflection-violet">
                <stop offset="0" stop-color="#bca5fa" stop-opacity=".36"/>
                <stop offset=".36" stop-color="#7564bd" stop-opacity=".18"/>
                <stop offset="1" stop-color="#3e3277" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <path d="M0 0L690 310V530L0 900Z" fill="url(#studio-wall-left)"/>
        <path d="M1600 0L910 310V530L1600 900Z" fill="url(#studio-wall-right)"/>
        <path d="M0 900L690 530H910L1600 900Z" fill="url(#studio-floor)"/>
        <rect x="690" y="310" width="220" height="220" fill="#0c1325" fill-opacity=".24"/>
        <ellipse cx="800" cy="530" rx="430" ry="250" fill="url(#studio-vanishing-light)"/>
        <!-- A subtle illuminated room, not a second mirrored 3D renderer.
             JS ties the three colored projections to actual ribbon rotation. -->
        <g class="studio-room__reflection" data-studio-reflection="floor" clip-path="url(#studio-room-floor-clip)" opacity="0">
            <ellipse cx="780" cy="647" rx="355" ry="135" transform="rotate(-8 780 647)" fill="url(#studio-room-reflection-blue)"/>
            <ellipse cx="910" cy="653" rx="260" ry="111" transform="rotate(8 910 653)" fill="url(#studio-room-reflection-violet)"/>
        </g>
        <g class="studio-room__reflection" data-studio-reflection="left" clip-path="url(#studio-room-left-clip)" opacity="0">
            <ellipse cx="490" cy="437" rx="225" ry="225" transform="rotate(-31 490 437)" fill="url(#studio-room-reflection-blue)"/>
        </g>
        <g class="studio-room__reflection" data-studio-reflection="right" clip-path="url(#studio-room-right-clip)" opacity="0">
            <ellipse cx="1100" cy="416" rx="220" ry="245" transform="rotate(27 1100 416)" fill="url(#studio-room-reflection-violet)"/>
        </g>
        <g stroke="#7894d9" stroke-width="1" stroke-opacity=".095" fill="none">
            <path d="M0 0L690 310H910L1600 0"/>
            <path d="M0 900L690 530H910L1600 900"/>
            <path d="M690 310V530M910 310V530"/>
        </g>
        <g stroke="#8a78d3" stroke-width=".7" stroke-opacity=".05" fill="none">
            <path d="M0 330L690 390H910L1600 330"/>
            <path d="M0 630L690 487H910L1600 630"/>
        </g>
    </svg>
    <div class="studio-room__atmosphere"></div>
</div>
