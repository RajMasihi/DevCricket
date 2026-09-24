</div>

<!-- Mobile Bottom Navigation (Hidden on Desktop) -->
<div class="mobile-bottom-nav d-md-none">
    <div class="mobile-nav-item">
        <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    </div>
    <div class="mobile-nav-item">
        <a href="{{ route('upcoming-matches') }}" class="{{ request()->is('cricket-schedule/upcoming') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Matches</span>
        </a>
    </div>
    <div class="mobile-nav-item">
        <a href="{{ route('series') }}" class="{{ request()->is('cricket-series') ? 'active' : '' }}">
            <i class="fas fa-trophy"></i>
            <span>Series</span>
        </a>
    </div>
    <div class="mobile-nav-item">
        <a href="{{ route('news') }}" class="{{ request()->is('cricket-news*') ? 'active' : '' }}">
            <i class="fas fa-newspaper"></i>
            <span>News</span>
        </a>
    </div>
    <div class="mobile-nav-item">
        <a href="{{ route('icc-rankings-mens') }}" class="{{ request()->is('icc-rankings*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            <span>Ranks</span>
        </a>
    </div>
</div>

<!-- Desktop Footer (Hidden on Mobile) -->
<footer class="d-none d-md-block">
    <footer class="whitefoter">
        <div class="whitefooter-cont">

            <div class="copy-right">
                Copyright © {{ date('Y') }} Criclivem. All rights reserved.
            </div>

            <nav class="nav">
                <ul>
                    <li>
                        <span><i class="fa fa-caret-right" aria-hidden="true"></i></span>
                        <a href="{{ route('about') }}">About</a>&nbsp;
                    </li>
                    <li>
                        <span><i class="fa fa-caret-right" aria-hidden="true"></i></span>
                        <a href="{{ route('contact') }}">Contact</a>&nbsp;
                    </li>
                    <li>
                        <span><i class="fa fa-caret-right" aria-hidden="true"></i></span>
                        <a href="{{ route('privacy') }}">Privacy Policy</a>&nbsp;
                    </li>
                    <!-- <li>
                        <span><i class="fa fa-caret-right" aria-hidden="true"></i></span>
                        <a href="{{ route('sitemap') }}">Sitemap</a>
                    </li> -->
                </ul>
            </nav>
        </div>

    </footer>
</footer>
</body>

</html>
