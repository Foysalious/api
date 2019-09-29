<?php namespace Sheba\Location\Distance;

class DistanceStrategy
{
    public static $HAVERSINE = "haversine";
    public static $COSINE = "cosine";
    public static $ELLIPSOIDAL_PLANE = "ellipsoidal_plane";
    public static $SPHERICAL_PLANE = "spherical_plane";
    public static $VINCENTY = "vincenty";
    public static $GOOGLE_DISTANCE_MATRIX = "google_distance_matrix";
}